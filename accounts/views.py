"""
Accounts app views - Authentication views.
"""
from django.shortcuts import render, redirect
from django.contrib.auth import login, logout, authenticate
from django.contrib.auth.decorators import login_required
from django.contrib import messages
from django.urls import reverse
from .forms import RegisterForm, LoginForm


def register(request):
    """User registration view."""
    if request.user.is_authenticated:
        return redirect('dashboard:index')
    
    if request.method == 'POST':
        form = RegisterForm(request.POST)
        if form.is_valid():
            user = form.save()
            login(request, user)
            messages.success(request, 'ثبت‌نام با موفقیت انجام شد.')
            return redirect('dashboard:index')
    else:
        form = RegisterForm()
    
    context = {'form': form}
    return render(request, 'accounts/register.html', context)


def login_view(request):
    """User login view."""
    if request.user.is_authenticated:
        return redirect('dashboard:index')
    
    if request.method == 'POST':
        form = LoginForm(request.POST)
        if form.is_valid():
            mobile = form.cleaned_data['mobile']
            password = form.cleaned_data['password']
            
            user = authenticate(request, username=mobile, password=password)
            if user:
                login(request, user)
                messages.success(request, 'ورود با موفقیت انجام شد.')
                
                next_url = request.GET.get('next')
                if next_url:
                    return redirect(next_url)
                return redirect('dashboard:index')
            else:
                messages.error(request, 'شماره موبایل یا رمز عبور اشتباه است.')
    else:
        form = LoginForm()
    
    context = {'form': form}
    return render(request, 'accounts/login.html', context)


@login_required
def logout_view(request):
    """User logout view."""
    logout(request)
    messages.info(request, 'با موفقیت خارج شدید.')
    return redirect('home')
