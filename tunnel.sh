#!/usr/bin/env bash
set -uo pipefail

# ─── Colors ───────────────────────────────────────────────
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
CYAN='\033[0;36m'
BOLD='\033[1m'
NC='\033[0m'

# ─── Helpers ──────────────────────────────────────────────
info()  { printf "${CYAN}ℹ  %s${NC}\n" "$*"; }
ok()    { printf "${GREEN}✔  %s${NC}\n" "$*"; }
warn()  { printf "${YELLOW}⚠  %s${NC}\n" "$*"; }
err()   { printf "${RED}✖  %s${NC}\n" "$*" >&2; }
banner() {
  printf "\n${BOLD}──────────────────────────────────────────${NC}\n"
  printf "${BOLD}  🌐  Cloudflare Tunnel Helper${NC}\n"
  printf "${BOLD}──────────────────────────────────────────${NC}\n\n"
}

usage() {
  printf "Usage: ${BOLD}./tunnel.sh <port> [options]${NC}\n\n"
  printf "Options:\n"
  printf "  ${BOLD}--detach${NC}     Start tunnel in background and exit\n"
  printf "  ${BOLD}--stop${NC}       Stop a running tunnel\n"
  printf "  ${BOLD}--status${NC}     Show status of running tunnel\n"
  printf "\nExamples:\n"
  printf "  ${GREEN}./tunnel.sh 9000${NC}              # Start tunnel (foreground)\n"
  printf "  ${GREEN}./tunnel.sh 8080 --detach${NC}     # Start tunnel (background)\n"
  printf "  ${GREEN}./tunnel.sh --stop${NC}            # Stop running tunnel\n"
  printf "  ${GREEN}./tunnel.sh --status${NC}          # Check tunnel status\n\n"
  exit 1
}

cleanup() {
  if [ -n "${CF_PID:-}" ] && kill -0 "$CF_PID" 2>/dev/null; then
    info "Stopping tunnel (PID: $CF_PID)..."
    kill "$CF_PID" 2>/dev/null || true
    wait "$CF_PID" 2>/dev/null || true
    ok "Tunnel stopped."
  fi
  rm -f "${CF_LOG:-}"
}

# ─── State file ───────────────────────────────────────────
STATE_DIR="${HOME}/.cache/tunnel.sh"
STATE_FILE="${STATE_DIR}/tunnel.pid"
URL_FILE="${STATE_DIR}/tunnel.url"
LOG_FILE="${STATE_DIR}/tunnel.log"

ensure_state_dir() { mkdir -p "$STATE_DIR"; }

save_state() {
  ensure_state_dir
  echo "$1" > "$STATE_FILE"
  echo "$2" > "$URL_FILE"
}

clear_state() {
  rm -f "$STATE_FILE" "$URL_FILE" "${LOG_FILE:-}"
}

is_running() {
  if [ -f "$STATE_FILE" ]; then
    local pid
    pid=$(cat "$STATE_FILE")
    if kill -0 "$pid" 2>/dev/null; then
      return 0
    fi
  fi
  return 1
}

# ─── Stop command ─────────────────────────────────────────
do_stop() {
  if is_running; then
    local pid
    pid=$(cat "$STATE_FILE")
    info "Stopping tunnel (PID: $pid)..."
    kill "$pid" 2>/dev/null || true
    sleep 1
    kill -9 "$pid" 2>/dev/null || true
    clear_state
    ok "Tunnel stopped."
  else
    warn "No running tunnel found."
    # Also kill any orphaned cloudflared processes
    local orphans
    orphans=$(pgrep -f "cloudflared tunnel" 2>/dev/null || true)
    if [ -n "$orphans" ]; then
      warn "Killing orphaned cloudflared processes: $orphans"
      echo "$orphans" | xargs kill 2>/dev/null || true
      ok "Done."
    fi
  fi
  exit 0
}

# ─── Status command ───────────────────────────────────────
do_status() {
  if is_running; then
    local pid url code
    pid=$(cat "$STATE_FILE")
    url=$(cat "$URL_FILE" 2>/dev/null || echo "unknown")
    ok "Tunnel is running"
    printf "  ${BOLD}PID:${NC}  %s\n" "$pid"
    printf "  ${BOLD}URL:${NC}  ${CYAN}%s${NC}\n" "$url"

    # Quick health check
    code=$(curl -s -o /dev/null -w '%{http_code}' --max-time 5 "$url" 2>/dev/null) || true
    if [ -z "$code" ]; then code="000"; fi
    if [ "$code" = "200" ]; then
      ok "Health check: HTTP $code ✅"
    else
      warn "Health check: HTTP $code"
    fi
  else
    warn "No running tunnel found."
  fi
  exit 0
}

# ─── Parse args ───────────────────────────────────────────
DETACH=false
PORT=""

for arg in "$@"; do
  case "$arg" in
    --stop)   do_stop ;;
    --status) do_status ;;
    --detach) DETACH=true ;;
    --help|-h) usage ;;
    -*)       err "Unknown option: $arg"; usage ;;
    *)        PORT="$arg" ;;
  esac
done

# ─── Banner ───────────────────────────────────────────────
banner

# ─── Validate input ──────────────────────────────────────
if [ -z "$PORT" ]; then
  err "No port specified."
  usage
fi

if ! [[ "$PORT" =~ ^[0-9]+$ ]] || [ "$PORT" -lt 1 ] || [ "$PORT" -gt 65535 ]; then
  err "Invalid port: $PORT (must be 1-65535)"
  exit 1
fi

# ─── Check if cloudflared is installed ────────────────────
if ! command -v cloudflared &>/dev/null; then
  err "cloudflared is not installed."
  printf "\nInstall it with:\n"
  printf "  ${YELLOW}curl -L https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64 -o /usr/local/bin/cloudflared && chmod +x /usr/local/bin/cloudflared${NC}\n\n"
  exit 1
fi

# ─── Stop existing tunnel if running ─────────────────────
if is_running; then
  warn "An existing tunnel is running. Stopping it first..."
  do_stop
fi

# ─── Check if port is open ───────────────────────────────
info "Checking port ${BOLD}$PORT${NC}..."

if ! ss -tlnp 2>/dev/null | grep -q ":${PORT} "; then
  err "Nothing is listening on port $PORT."
  printf "\n  Start your app first, then try again.\n\n"
  exit 1
fi

# ─── HTTP health check ───────────────────────────────────
info "Running HTTP health check..."

HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" --max-time 5 "http://127.0.0.1:${PORT}" 2>/dev/null) || true
if [ -z "$HTTP_CODE" ]; then HTTP_CODE="000"; fi

if [ "$HTTP_CODE" = "000" ]; then
  err "Port $PORT is listening but not responding to HTTP."
  printf "\n  The service might still be starting up. Try again in a moment.\n\n"
  exit 1
fi

ok "Port $PORT is active (HTTP $HTTP_CODE)"

# ─── Start tunnel ─────────────────────────────────────────
info "Starting Cloudflare tunnel..."
printf "\n"

CF_LOG="$LOG_FILE"
ensure_state_dir
> "$CF_LOG"

setsid cloudflared tunnel \
  --url "http://127.0.0.1:${PORT}" \
  --protocol http2 \
  --no-autoupdate \
  </dev/null >"$CF_LOG" 2>&1 &
CF_PID=$!

# Wait for the URL to appear (max 30s)
TUNNEL_URL=""
for i in $(seq 1 30); do
  sleep 1
  TUNNEL_URL=$(grep -o 'https://[a-zA-Z0-9_-]*\.trycloudflare\.com' "$CF_LOG" 2>/dev/null | tail -1) || true
  if [ -n "$TUNNEL_URL" ]; then
    break
  fi
  # Check if cloudflared died
  if ! kill -0 "$CF_PID" 2>/dev/null; then
    err "cloudflared process died unexpectedly."
    printf "\n  Logs:\n"
    sed 's/^/    /' "$CF_LOG"
    printf "\n"
    exit 1
  fi
done

if [ -z "$TUNNEL_URL" ]; then
  err "Failed to get tunnel URL within 30 seconds."
  printf "\n  Logs:\n"
  sed 's/^/    /' "$CF_LOG"
  printf "\n"
  exit 1
fi

# ─── Save state ───────────────────────────────────────────
save_state "$CF_PID" "$TUNNEL_URL"

# ─── Verify tunnel is reachable ───────────────────────────
info "Verifying tunnel..."
sleep 2

VERIFY_CODE=$(curl -s -o /dev/null -w '%{http_code}' --max-time 10 "$TUNNEL_URL" 2>/dev/null) || true
if [ -z "$VERIFY_CODE" ]; then VERIFY_CODE="000"; fi

# ─── Print result ─────────────────────────────────────────
printf "\n"
printf "${BOLD}──────────────────────────────────────────${NC}\n"
printf "${GREEN}${BOLD}  ✅  Your tunnel is live!${NC}\n"
printf "${BOLD}──────────────────────────────────────────${NC}\n\n"
printf "  ${BOLD}Port:${NC}    %s\n" "$PORT"
printf "  ${BOLD}URL:${NC}     ${CYAN}%s${NC}\n" "$TUNNEL_URL"
printf "  ${BOLD}PID:${NC}     %s\n" "$CF_PID"
printf "\n"

if [ "$VERIFY_CODE" = "200" ]; then
  ok "Health check passed (HTTP $VERIFY_CODE)"
elif [ "$VERIFY_CODE" = "530" ]; then
  warn "Got HTTP 530 — Cloudflare may still be propagating. Try in a few seconds."
else
  warn "Health check returned HTTP $VERIFY_CODE — try the link in your browser."
fi

printf "\n"
printf "  ${BOLD}Commands:${NC}\n"
printf "    ${GREEN}./tunnel.sh --status${NC}   Check if tunnel is running\n"
printf "    ${GREEN}./tunnel.sh --stop${NC}     Stop the tunnel\n"
printf "\n"

# ─── Detach or foreground ─────────────────────────────────
if [ "$DETACH" = true ]; then
  ok "Tunnel running in background (PID: $CF_PID). Use './tunnel.sh --stop' to stop."
  exit 0
else
  printf "  Press ${BOLD}Ctrl+C${NC} to stop the tunnel.\n\n"
  info "Watching tunnel..."
  trap cleanup EXIT
  wait "$CF_PID" 2>/dev/null || true
  cleanup
fi
