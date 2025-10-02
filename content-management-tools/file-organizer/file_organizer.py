#!/usr/bin/env python3
"""
File Organizer
Automated file naming, organization, and duplicate detection
"""

import os
import hashlib
from pathlib import Path
from datetime import datetime
import shutil
import re

class FileOrganizer:
    def __init__(self, source_dir):
        self.source_dir = Path(source_dir)
        self.file_hashes = {}
    
    def calculate_hash(self, file_path):
        """Calculate MD5 hash of a file"""
        hash_md5 = hashlib.md5()
        with open(file_path, "rb") as f:
            for chunk in iter(lambda: f.read(4096), b""):
                hash_md5.update(chunk)
        return hash_md5.hexdigest()
    
    def find_duplicates(self):
        """Find duplicate files based on hash"""
        duplicates = []
        files = [f for f in self.source_dir.rglob('*') if f.is_file()]
        
        print(f"Scanning {len(files)} files for duplicates...")
        
        for file_path in files:
            file_hash = self.calculate_hash(file_path)
            
            if file_hash in self.file_hashes:
                duplicates.append({
                    'original': self.file_hashes[file_hash],
                    'duplicate': file_path
                })
            else:
                self.file_hashes[file_hash] = file_path
        
        return duplicates
    
    def normalize_filename(self, filename):
        """Normalize filename to standard format"""
        # Remove special characters
        filename = re.sub(r'[^\w\s.-]', '', filename)
        # Replace spaces with hyphens
        filename = re.sub(r'\s+', '-', filename)
        # Convert to lowercase
        filename = filename.lower()
        # Remove multiple hyphens
        filename = re.sub(r'-+', '-', filename)
        return filename
    
    def organize_by_type(self, output_dir):
        """Organize files by type into subdirectories"""
        output_dir = Path(output_dir)
        output_dir.mkdir(parents=True, exist_ok=True)
        
        file_types = {
            'images': {'.jpg', '.jpeg', '.png', '.gif', '.bmp', '.svg', '.webp'},
            'documents': {'.pdf', '.doc', '.docx', '.txt', '.rtf', '.odt'},
            'videos': {'.mp4', '.avi', '.mov', '.wmv', '.flv', '.mkv'},
            'audio': {'.mp3', '.wav', '.flac', '.aac', '.ogg', '.m4a'},
            'archives': {'.zip', '.rar', '.7z', '.tar', '.gz'},
            'code': {'.php', '.js', '.css', '.html', '.py', '.java', '.cpp'},
        }
        
        files = [f for f in self.source_dir.rglob('*') if f.is_file()]
        
        for file_path in files:
            ext = file_path.suffix.lower()
            
            # Determine category
            category = 'other'
            for cat, extensions in file_types.items():
                if ext in extensions:
                    category = cat
                    break
            
            # Create category directory
            category_dir = output_dir / category
            category_dir.mkdir(exist_ok=True)
            
            # Normalize filename
            new_name = self.normalize_filename(file_path.name)
            new_path = category_dir / new_name
            
            # Handle filename conflicts
            counter = 1
            while new_path.exists():
                stem = Path(new_name).stem
                suffix = Path(new_name).suffix
                new_name = f"{stem}-{counter}{suffix}"
                new_path = category_dir / new_name
                counter += 1
            
            # Copy file
            shutil.copy2(file_path, new_path)
            print(f"Organized: {file_path.name} -> {category}/{new_name}")
        
        print(f"\nFiles organized into {output_dir}")
    
    def rename_with_date_prefix(self):
        """Add date prefix to filenames based on modification date"""
        files = [f for f in self.source_dir.rglob('*') if f.is_file()]
        
        for file_path in files:
            # Get modification time
            mod_time = datetime.fromtimestamp(file_path.stat().st_mtime)
            date_prefix = mod_time.strftime('%Y%m%d')
            
            # Create new filename
            new_name = f"{date_prefix}-{file_path.name}"
            new_path = file_path.parent / new_name
            
            # Rename if not already prefixed
            if not file_path.name.startswith(date_prefix):
                try:
                    file_path.rename(new_path)
                    print(f"Renamed: {file_path.name} -> {new_name}")
                except Exception as e:
                    print(f"Error renaming {file_path.name}: {e}")
    
    def generate_report(self, output_file='file_report.txt'):
        """Generate a report of files in the directory"""
        files = [f for f in self.source_dir.rglob('*') if f.is_file()]
        
        # Count by extension
        extension_counts = {}
        total_size = 0
        
        for file_path in files:
            ext = file_path.suffix.lower() or 'no extension'
            extension_counts[ext] = extension_counts.get(ext, 0) + 1
            total_size += file_path.stat().st_size
        
        # Generate report
        report_path = self.source_dir / output_file
        with open(report_path, 'w') as f:
            f.write(f"File Organization Report\n")
            f.write(f"Generated: {datetime.now().strftime('%Y-%m-%d %H:%M:%S')}\n")
            f.write(f"Directory: {self.source_dir}\n")
            f.write(f"\n{'='*60}\n\n")
            f.write(f"Total Files: {len(files)}\n")
            f.write(f"Total Size: {total_size / (1024*1024):.2f} MB\n")
            f.write(f"\nFiles by Extension:\n")
            f.write(f"{'-'*60}\n")
            
            for ext, count in sorted(extension_counts.items(), key=lambda x: x[1], reverse=True):
                f.write(f"{ext:20s}: {count:5d}\n")
        
        print(f"Report saved to {report_path}")

def main():
    import argparse
    
    parser = argparse.ArgumentParser(description='File Organization Tool')
    parser.add_argument('source_dir', help='Source directory to organize')
    parser.add_argument('--find-duplicates', action='store_true', help='Find duplicate files')
    parser.add_argument('--organize', help='Organize files into output directory')
    parser.add_argument('--add-dates', action='store_true', help='Add date prefix to filenames')
    parser.add_argument('--report', action='store_true', help='Generate file report')
    
    args = parser.parse_args()
    
    organizer = FileOrganizer(args.source_dir)
    
    if args.find_duplicates:
        duplicates = organizer.find_duplicates()
        if duplicates:
            print(f"\nFound {len(duplicates)} duplicate files:")
            for dup in duplicates:
                print(f"  Original: {dup['original']}")
                print(f"  Duplicate: {dup['duplicate']}")
                print()
        else:
            print("No duplicates found.")
    
    if args.organize:
        organizer.organize_by_type(args.organize)
    
    if args.add_dates:
        organizer.rename_with_date_prefix()
    
    if args.report:
        organizer.generate_report()

if __name__ == '__main__':
    main()
