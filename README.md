#  Bulk CSV Import + Chunked Drag-and-Drop Image Upload

## Overview

It demonstrates:
- Bulk CSV import with upsert logic
- Chunked, resumable drag-and-drop image uploads
- Automatic product-image linking
- Image variant generation (256px / 512px / 1024px)
- Concurrency-safe background processing using queues

## Domain Choice
**Products**  
- Unique key: **SKU**
- Each product can have one primary image and multiple image variants.

---

## High-Level Flow

1. **Upload CSV**
   - CSV contains product data (SKU, name, price)
   - Products are created or updated (upsert)
   - Import summary is stored (total, imported, updated, invalid, duplicates)

2. **Upload Images (Folder / Drag & Drop)**
   - Images are uploaded **after CSV**
   - Image filename must match product SKU (e.g. `SKU1001.jpg`)
   - Images are uploaded in chunks
   - On completion, images are automatically linked to products

3. **Background Processing**
   - Image variants are generated asynchronously
   - Primary image is assigned idempotently

---

## CSV Import Details

### CSV Format
```csv
sku,name,price
SKU1001,Product One,100
SKU1002,Product Two,200
