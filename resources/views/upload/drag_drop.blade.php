<!DOCTYPE html>
<html>
<head>
    <title>Bulk Image Upload (Filename = SKU)</title>
</head>
<body>

<h3>Upload Product Images (Filename must be SKU)</h3>

<div id="dropZone" style="
    width:500px;
    height:250px;
    border:2px dashed #999;
    display:flex;
    align-items:center;
    justify-content:center;
">
    Drop image folder here
</div>

<p id="status"></p>

<script>
const dropZone = document.getElementById('dropZone');
const status = document.getElementById('status');
const CHUNK_SIZE = 1024 * 1024;

dropZone.addEventListener('dragover', e => e.preventDefault());

dropZone.addEventListener('drop', async e => {
    e.preventDefault();

    const items = e.dataTransfer.items;
    if (!items) return;

    const files = [];

    // 🔥 READ FILES FROM FOLDER
    for (const item of items) {
        const entry = item.webkitGetAsEntry();
        if (entry) {
            await traverseEntry(entry, files);
        }
    }

    if (!files.length) {
        status.innerText = 'No images found in folder';
        return;
    }

    for (const file of files) {
        await uploadFile(file);
    }

    status.innerText = 'All images uploaded & linked successfully!';
});

// 🔹 RECURSIVELY READ FOLDER
function traverseEntry(entry, files) {
    return new Promise(resolve => {
        if (entry.isFile) {
            entry.file(file => {
                files.push(file);
                resolve();
            });
        } else if (entry.isDirectory) {
            const reader = entry.createReader();
            reader.readEntries(async entries => {
                for (const ent of entries) {
                    await traverseEntry(ent, files);
                }
                resolve();
            });
        }
    });
}

// 🔹 UPLOAD SINGLE FILE (CHUNKED)
async function uploadFile(file) {
    const sku = file.name.split('.').slice(0, -1).join('.');
    const totalChunks = Math.ceil(file.size / CHUNK_SIZE);

    status.innerText = `Uploading image for SKU: ${sku}`;

    for (let i = 0; i < totalChunks; i++) {
        const chunk = file.slice(i * CHUNK_SIZE, (i + 1) * CHUNK_SIZE);

        const fd = new FormData();
        fd.append('upload_id', sku);
        fd.append('chunk_index', i);
        fd.append('chunk', chunk);

        await fetch('/upload/chunk', {
            method: 'POST',
            body: fd,
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
    }

    const completeFd = new FormData();
    completeFd.append('upload_id', sku);
    completeFd.append('total_chunks', totalChunks);

    await fetch('/upload/complete', {
        method: 'POST',
        body: completeFd,
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    });
}
</script>


</body>
</html>
