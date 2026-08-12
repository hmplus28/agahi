"""
Persian text normalization utilities.
Handles Arabic characters, zero-width characters, and half-spaces.
"""
import re


class PersianNormalizer:
    """Normalizes Persian text for consistent storage and comparison."""
    
    # Arabic to Persian character mapping
    ARABIC_TO_PERSIAN = {
        'ي': 'ی',
        'ك': 'ک',
        'دِ': 'د',
        'بِ': 'ب',
        'زِ': 'ز',
        'ذِ': 'ذ',
        'شِ': 'ش',
        'سِ': 'س',
        'ى': 'ی',
        'ك': 'ک',
        '٠': '۰',
        '١': '۱',
        '٢': '۲',
        '٣': '۳',
        '٤': '۴',
        '٥': '۵',
        '٦': '۶',
        '٧': '۷',
        '٨': '۸',
        '٩': '۹',
    }
    
    # Zero-width characters
    ZERO_WIDTH_SPACE = '\u200c'  # Half-space (نیم‌فاصله)
    ZERO_WIDTH_NON_JOINER = '\u200b'
    ZERO_WIDTH_JOINER = '\u200d'
    
    def __init__(self):
        self.pattern_arabic = re.compile('|'.join(self.ARABIC_TO_PERSIAN.keys()))
    
    def normalize(self, text: str) -> str:
        """
        Normalize Persian text:
        1. Convert Arabic characters to Persian
        2. Remove unwanted zero-width characters
        3. Standardize half-spaces
        """
        if not text:
            return text
        
        # Convert Arabic characters to Persian
        normalized = self.pattern_arabic.sub(
            lambda match: self.ARABIC_TO_PERSIAN[match.group(0)],
            text
        )
        
        # Remove zero-width non-joiner and joiner (keep only proper half-space)
        normalized = normalized.replace(self.ZERO_WIDTH_NON_JOINER, '')
        normalized = normalized.replace(self.ZERO_WIDTH_JOINER, '')
        
        # Normalize multiple spaces to single space
        normalized = re.sub(r'\s+', ' ', normalized)
        
        # Strip leading/trailing whitespace
        normalized = normalized.strip()
        
        return normalized
    
    def normalize_for_search(self, text: str) -> str:
        """
        Normalize text for search/comparison purposes.
        Removes all spaces and half-spaces for fuzzy matching.
        """
        normalized = self.normalize(text)
        # Remove all whitespace and half-spaces
        normalized = re.sub(r'[\s\u200c]', '', normalized)
        return normalized.lower()


# Singleton instance
normalizer = PersianNormalizer()


def normalize_persian_text(text: str) -> str:
    """Convenience function for normalizing Persian text."""
    return normalizer.normalize(text)


def normalize_for_comparison(text: str) -> str:
    """Convenience function for normalizing text for comparison."""
    return normalizer.normalize_for_search(text)
