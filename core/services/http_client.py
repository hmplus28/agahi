"""Minimal optional HTTP client built on Python's standard library.

It is used only by integrations explicitly enabled by operators; the public site
never imports a third-party network client at startup.
"""
from dataclasses import dataclass
import json
from urllib.error import HTTPError, URLError
from urllib.parse import urlencode
from urllib.request import Request, urlopen


class HTTPClientError(RuntimeError):
    pass


@dataclass(frozen=True)
class HTTPResponse:
    status_code: int
    text: str
    data: dict


def _request(url: str, payload: bytes, content_type: str, timeout: int) -> HTTPResponse:
    request = Request(url, data=payload, method='POST', headers={'Content-Type': content_type, 'Accept': 'application/json'})
    try:
        with urlopen(request, timeout=timeout) as response:  # noqa: S310 - URL is provider-defined in application code.
            text = response.read().decode('utf-8', errors='replace')
            status_code = response.status
    except HTTPError as exc:
        detail = exc.read().decode('utf-8', errors='replace') if exc.fp else ''
        raise HTTPClientError(f'HTTP {exc.code}: {detail[:500]}') from exc
    except URLError as exc:
        raise HTTPClientError(f'network error: {exc.reason}') from exc
    except OSError as exc:
        raise HTTPClientError(f'network error: {exc}') from exc
    if not 200 <= status_code < 300:
        raise HTTPClientError(f'HTTP {status_code}')
    try:
        data = json.loads(text) if text else {}
    except json.JSONDecodeError as exc:
        raise HTTPClientError('response is not valid JSON') from exc
    if not isinstance(data, dict):
        raise HTTPClientError('response JSON must be an object')
    return HTTPResponse(status_code=status_code, text=text, data=data)


def post_json(url: str, payload: dict, timeout: int) -> HTTPResponse:
    return _request(url, json.dumps(payload, ensure_ascii=False).encode('utf-8'), 'application/json; charset=utf-8', timeout)


def post_form(url: str, payload: dict, timeout: int) -> HTTPResponse:
    return _request(url, urlencode(payload).encode('utf-8'), 'application/x-www-form-urlencoded; charset=utf-8', timeout)
