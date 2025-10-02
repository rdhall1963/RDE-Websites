#!/usr/bin/env python3
"""
Gallery Management Tool
Automates image processing, optimization, and gallery generation
"""

import os
import sys
from PIL import Image
from pathlib import Path
import json

class GalleryManager:
    def __init__(self, source_dir, output_dir):
        self.source_dir = Path(source_dir)
        self.output_dir = Path(output_dir)
        self.thumbnails_dir = self.output_dir / 'thumbnails'
        self.medium_dir = self.output_dir / 'medium'
        self.large_dir = self.output_dir / 'large'
        
        # Create output directories
        for directory in [self.thumbnails_dir, self.medium_dir, self.large_dir]:
            directory.mkdir(parents=True, exist_ok=True)
    
    def optimize_image(self, image_path, output_path, max_size=None, quality=85):
        """Optimize and resize image"""
        try:
            with Image.open(image_path) as img:
                # Convert RGBA to RGB if needed
                if img.mode == 'RGBA':
                    img = img.convert('RGB')
                
                # Resize if max_size is specified
                if max_size:
                    img.thumbnail(max_size, Image.Resampling.LANCZOS)
                
                # Save optimized image
                img.save(output_path, 'JPEG', quality=quality, optimize=True)
                return True
        except Exception as e:
            print(f"Error processing {image_path}: {e}")
            return False
    
    def process_images(self):
        """Process all images in source directory"""
        supported_formats = {'.jpg', '.jpeg', '.png', '.gif', '.bmp'}
        images = [f for f in self.source_dir.iterdir() 
                 if f.suffix.lower() in supported_formats]
        
        gallery_data = []
        
        for image_path in images:
            print(f"Processing {image_path.name}...")
            
            # Generate output filenames
            base_name = image_path.stem + '.jpg'
            thumb_path = self.thumbnails_dir / base_name
            medium_path = self.medium_dir / base_name
            large_path = self.large_dir / base_name
            
            # Create different sizes
            self.optimize_image(image_path, thumb_path, max_size=(300, 300), quality=80)
            self.optimize_image(image_path, medium_path, max_size=(800, 800), quality=85)
            self.optimize_image(image_path, large_path, max_size=(1920, 1920), quality=90)
            
            # Get image dimensions
            with Image.open(image_path) as img:
                width, height = img.size
            
            # Add to gallery data
            gallery_data.append({
                'name': image_path.stem,
                'thumbnail': f'thumbnails/{base_name}',
                'medium': f'medium/{base_name}',
                'large': f'large/{base_name}',
                'original_width': width,
                'original_height': height
            })
        
        # Save gallery metadata
        metadata_path = self.output_dir / 'gallery-metadata.json'
        with open(metadata_path, 'w') as f:
            json.dump(gallery_data, f, indent=2)
        
        print(f"\nProcessed {len(images)} images")
        print(f"Gallery metadata saved to {metadata_path}")
    
    def generate_html(self):
        """Generate HTML gallery template"""
        metadata_path = self.output_dir / 'gallery-metadata.json'
        
        if not metadata_path.exists():
            print("No gallery metadata found. Run process_images first.")
            return
        
        with open(metadata_path, 'r') as f:
            gallery_data = json.load(f)
        
        html_template = """<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Gallery</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; }
        .gallery-item { background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); transition: transform 0.3s; cursor: pointer; }
        .gallery-item:hover { transform: translateY(-5px); box-shadow: 0 4px 12px rgba(0,0,0,0.2); }
        .gallery-item img { width: 100%; height: 250px; object-fit: cover; display: block; }
        .gallery-item-name { padding: 15px; font-size: 14px; text-align: center; }
        .lightbox { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 1000; justify-content: center; align-items: center; }
        .lightbox.active { display: flex; }
        .lightbox img { max-width: 90%; max-height: 90%; }
        .lightbox-close { position: absolute; top: 20px; right: 40px; font-size: 40px; color: white; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Image Gallery</h1>
    <div class="gallery">
"""
        
        for item in gallery_data:
            html_template += f"""
        <div class="gallery-item" onclick="openLightbox('{item['large']}')">
            <img src="{item['thumbnail']}" alt="{item['name']}" loading="lazy">
            <div class="gallery-item-name">{item['name']}</div>
        </div>
"""
        
        html_template += """
    </div>
    
    <div class="lightbox" id="lightbox" onclick="closeLightbox()">
        <span class="lightbox-close">&times;</span>
        <img id="lightbox-img" src="" alt="">
    </div>
    
    <script>
        function openLightbox(src) {
            document.getElementById('lightbox').classList.add('active');
            document.getElementById('lightbox-img').src = src;
        }
        
        function closeLightbox() {
            document.getElementById('lightbox').classList.remove('active');
        }
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeLightbox();
        });
    </script>
</body>
</html>
"""
        
        html_path = self.output_dir / 'gallery.html'
        with open(html_path, 'w') as f:
            f.write(html_template)
        
        print(f"Gallery HTML saved to {html_path}")

def main():
    if len(sys.argv) < 3:
        print("Usage: python gallery_manager.py <source_directory> <output_directory>")
        sys.exit(1)
    
    source_dir = sys.argv[1]
    output_dir = sys.argv[2]
    
    manager = GalleryManager(source_dir, output_dir)
    manager.process_images()
    manager.generate_html()
    
    print("\nGallery generation complete!")

if __name__ == '__main__':
    main()
