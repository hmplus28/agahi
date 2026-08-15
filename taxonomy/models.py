from django.db import models
from django.utils.text import slugify
from django.utils.translation import gettext_lazy as _


class Category(models.Model):
    """
    Hierarchical category model for ads.
    Supports unlimited nesting levels.
    """
    parent = models.ForeignKey(
        'self',
        on_delete=models.CASCADE,
        null=True,
        blank=True,
        related_name='children',
        verbose_name=_('parent category'),
        limit_choices_to={'is_active': True},
    )
    title = models.CharField(_('title'), max_length=255)
    slug = models.SlugField(_('slug'), unique=True, max_length=300)
    description = models.TextField(_('description'), blank=True)
    seo_title = models.CharField(_('SEO title'), max_length=255, blank=True)
    seo_description = models.TextField(_('SEO description'), blank=True)
    sort_order = models.PositiveIntegerField(_('sort order'), default=0)
    is_active = models.BooleanField(_('is active'), default=True)
    created_at = models.DateTimeField(_('created at'), auto_now_add=True)
    updated_at = models.DateTimeField(_('updated at'), auto_now=True)
    
    class Meta:
        verbose_name = _('category')
        verbose_name_plural = _('categories')
        ordering = ['sort_order', 'title']
    
    def __str__(self):
        return self.title

    def get_absolute_url(self):
        from django.urls import reverse
        return reverse('taxonomy:category_detail_slug', kwargs={'pk': self.pk, 'slug': self.slug})
    
    def get_full_path(self):
        """Return full category path from root to this category."""
        path = []
        current = self
        while current:
            path.append(current)
            current = current.parent
        return list(reversed(path))
    
    def get_ancestors(self):
        """Return all ancestor categories."""
        ancestors = []
        current = self.parent
        while current:
            ancestors.append(current)
            current = current.parent
        return list(reversed(ancestors))
    
    def get_descendants(self):
        """Return all descendant categories recursively."""
        descendants = []
        for child in self.children.all():
            descendants.append(child)
            descendants.extend(child.get_descendants())
        return descendants
    
    @property
    def level(self):
        """Return the depth level of this category (0-indexed)."""
        level = 0
        current = self.parent
        while current:
            level += 1
            current = current.parent
        return level
    
    def save(self, *args, **kwargs):
        if not self.slug:
            base_slug = slugify(self.title)
            slug = base_slug
            counter = 1
            while Category.objects.filter(slug=slug).exists():
                slug = f"{base_slug}-{counter}"
                counter += 1
            self.slug = slug
        super().save(*args, **kwargs)
