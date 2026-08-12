"""
Image processing service for ad images.
Handles resizing, optimization, and thumbnail generation.
"""
import os
from PIL import Image
from io import BytesIO
from django.core.files.uploadedfile import InMemoryUploadedFile
from django.conf import settings


class ImageProcessor:
    """Service for processing ad images."""
    
    # Maximum image dimensions
    MAX_WIDTH = 1920
    MAX_HEIGHT = 1080
    
    # Thumbnail sizes
    THUMBNAIL_SIZES = {
        'small': (150, 150),
        'medium': (300, 300),
        'large': (600, 400),
    }
    
    # Quality settings
    JPEG_QUALITY = 85
    WEBP_QUALITY = 80
    
    # Supported formats
    SUPPORTED_FORMATS = ['JPEG', 'PNG', 'WEBP']
    
    def __init__(self):
        pass
    
    def process_image(self, image_file, max_width=None, max_height=None):
        """
        Process an image file: resize, optimize, and convert if needed.
        
        Args:
            image_file: Django UploadedFile object
            max_width: Maximum width (optional, uses default if not provided)
            max_height: Maximum height (optional, uses default if not provided)
            
        Returns:
            Processed image as InMemoryUploadedFile
        """
        max_width = max_width or self.MAX_WIDTH
        max_height = max_height or self.MAX_HEIGHT
        
        # Open image
        img = Image.open(image_file)
        
        # Convert to RGB if necessary (for PNG with transparency)
        if img.mode in ('RGBA', 'LA', 'P'):
            # Create white background for transparent images
            background = Image.new('RGB', img.size, (255, 255, 255))
            if img.mode == 'P':
                img = img.convert('RGBA')
            background.paste(img, mask=img.split()[-1] if img.mode == 'RGBA' else None)
            img = background
        elif img.mode != 'RGB':
            img = img.convert('RGB')
        
        # Resize if image is too large
        img = self._resize_image(img, max_width, max_height)
        
        # Optimize and save to bytes
        output = BytesIO()
        img.save(
            output,
            format='JPEG',
            quality=self.JPEG_QUALITY,
            optimize=True,
            progressive=True
        )
        output.seek(0)
        
        # Create new filename with .jpg extension
        original_name = os.path.splitext(image_file.name)[0]
        new_name = f"{original_name}.jpg"
        
        # Create InMemoryUploadedFile
        processed_file = InMemoryUploadedFile(
            output,
            'ImageField',
            new_name,
            'image/jpeg',
            output.getbuffer().nbytes,
            None
        )
        
        return processed_file
    
    def _resize_image(self, img, max_width, max_height):
        """Resize image while maintaining aspect ratio."""
        original_width, original_height = img.size
        
        # Check if resizing is needed
        if original_width <= max_width and original_height <= max_height:
            return img
        
        # Calculate new dimensions maintaining aspect ratio
        ratio = min(max_width / original_width, max_height / original_height)
        new_width = int(original_width * ratio)
        new_height = int(original_height * ratio)
        
        # Resize using high-quality resampling
        img = img.resize((new_width, new_height), Image.Resampling.LANCZOS)
        
        return img
    
    def generate_thumbnail(self, image_path, size='medium'):
        """
        Generate a thumbnail for an image.
        
        Args:
            image_path: Path to the original image
            size: Thumbnail size key ('small', 'medium', 'large')
            
        Returns:
            Path to the generated thumbnail
        """
        if size not in self.THUMBNAIL_SIZES:
            raise ValueError(f"Invalid size: {size}. Must be one of {list(self.THUMBNAIL_SIZES.keys())}")
        
        thumb_width, thumb_height = self.THUMBNAIL_SIZES[size]
        
        # Open image
        img = Image.open(image_path)
        
        # Convert to RGB if necessary
        if img.mode != 'RGB':
            img = img.convert('RGB')
        
        # Generate thumbnail (modifies image in place)
        img.thumbnail((thumb_width, thumb_height), Image.Resampling.LANCZOS)
        
        # Generate thumbnail filename
        base_name, ext = os.path.splitext(image_path)
        thumb_path = f"{base_name}_{size}.jpg"
        
        # Save thumbnail
        img.save(thumb_path, 'JPEG', quality=self.JPEG_QUALITY, optimize=True)
        
        return thumb_path
    
    def validate_image(self, image_file):
        """
        Validate an image file.
        
        Args:
            image_file: Django UploadedFile object
            
        Returns:
            Tuple of (is_valid: bool, error_message: str or None)
        """
        try:
            img = Image.open(image_file)
            img.verify()
            
            # Reopen after verify (verify closes the file)
            image_file.seek(0)
            img = Image.open(image_file)
            
            # Check format
            if img.format not in self.SUPPORTED_FORMATS:
                return False, f"Unsupported image format: {img.format}. Supported: {', '.join(self.SUPPORTED_FORMATS)}"
            
            # Check dimensions
            if img.width > 5000 or img.height > 5000:
                return False, "Image dimensions too large (max 5000x5000)"
            
            # Check file size (10MB max)
            image_file.seek(0, 2)  # Seek to end
            file_size = image_file.tell()
            image_file.seek(0)  # Reset position
            
            if file_size > 10 * 1024 * 1024:
                return False, "Image file size too large (max 10MB)"
            
            return True, None
            
        except Exception as e:
            return False, f"Invalid image file: {str(e)}"


# Singleton instance
image_processor = ImageProcessor()


def process_ad_image(image_file, **kwargs):
    """Convenience function for processing ad images."""
    return image_processor.process_image(image_file, **kwargs)


def generate_image_thumbnail(image_path, size='medium'):
    """Convenience function for generating thumbnails."""
    return image_processor.generate_thumbnail(image_path, size)


def validate_ad_image(image_file):
    """Convenience function for validating images."""
    return image_processor.validate_image(image_file)
