"""Forms for support tickets and messages."""
from django import forms

from .models import Ticket, TicketMessage


class TicketCreateForm(forms.ModelForm):
    message = forms.CharField(label='شرح درخواست', widget=forms.Textarea(attrs={'class': 'form-control', 'rows': 6}))

    class Meta:
        model = Ticket
        fields = ['subject', 'priority']
        widgets = {
            'subject': forms.TextInput(attrs={'class': 'form-control', 'maxlength': 255}),
            'priority': forms.Select(attrs={'class': 'form-control'}),
        }


class TicketMessageForm(forms.ModelForm):
    class Meta:
        model = TicketMessage
        fields = ['message']
        widgets = {'message': forms.Textarea(attrs={'class': 'form-control', 'rows': 5})}
