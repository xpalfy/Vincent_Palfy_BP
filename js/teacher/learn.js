function togglePDFUpload() {
    let pdfSelect = document.getElementById('pdfSelect');
    let pdfUpload = document.getElementById('pdfUpload');
    let pdfSelectLabel = document.getElementById('pdfLabel');
    pdfUpload.style.display = pdfUpload.style.display === 'none' ? 'block' : 'none';
    pdfSelect.style.display = pdfUpload.style.display === 'none' ? 'block' : 'none';
    pdfSelectLabel.style.display = pdfUpload.style.display === 'none' ? 'block' : 'none';
}