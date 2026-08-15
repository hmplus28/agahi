"""Authentication views."""
from django.contrib import messages
from django.contrib.auth import login, logout
from django.contrib.auth.decorators import login_required
from django.shortcuts import redirect, render
from django.utils.http import url_has_allowed_host_and_scheme
from django.views.decorators.http import require_POST

from .forms import LoginForm, RegisterForm


def _safe_next(request):
    next_url = request.POST.get('next') or request.GET.get('next')
    if next_url and url_has_allowed_host_and_scheme(next_url, {request.get_host()}, request.is_secure()):
        return next_url
    return None


def register(request):
    if request.user.is_authenticated:
        return redirect('dashboard:index')
    form = RegisterForm(request.POST or None)
    if request.method == 'POST' and form.is_valid():
        user = form.save()
        login(request, user)
        messages.success(request, 'ثبت‌نام با موفقیت انجام شد.')
        return redirect(_safe_next(request) or 'dashboard:index')
    return render(request, 'accounts/register.html', {'form': form, 'next': _safe_next(request)})


def login_view(request):
    if request.user.is_authenticated:
        return redirect('dashboard:index')
    form = LoginForm(request, data=request.POST or None)
    if request.method == 'POST' and form.is_valid():
        login(request, form.get_user())
        messages.success(request, 'ورود با موفقیت انجام شد.')
        return redirect(_safe_next(request) or 'dashboard:index')
    return render(request, 'accounts/login.html', {'form': form, 'next': _safe_next(request)})


@login_required
@require_POST
def logout_view(request):
    logout(request)
    messages.info(request, 'با موفقیت خارج شدید.')
    return redirect('core:home')
