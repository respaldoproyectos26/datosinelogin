<?php
require_once "../../helpers/auth.php";
require_once "../../helpers/url.php";
require_once "../../helpers/helpers_exams.php"; // luego creamos helpers_capacitacion
require_once "../../includes/header_base.php";

require_login();

# if (!auth()->can('subir_medios')) denegar_acceso();
?>

<div class="container py-4">
  <h2 class="fw-bold mb-4"><i class="bi bi-cloud-upload"></i> Subir Material</h2>

  <form id="uploadForm" enctype="multipart/form-data">
    
    <div class="mb-3">
      <label class="form-label">Título</label>
      <input type="text" name="titulo" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Descripción</label>
      <textarea name="descripcion" class="form-control"></textarea>
    </div>

    <div class="mb-3">
      <label class="form-label">Categoría</label>
      <input type="text" name="categoria" class="form-control">
    </div>

    <div class="mb-3 upload-box p-5 border rounded text-center bg-light" id="dropArea">
      <i class="bi bi-upload fs-1 text-primary"></i>
      <p class="mt-2 mb-0">Arrastra tu archivo aquí o haz clic para seleccionar</p>
      <input type="file" name="archivo" id="fileInput" class="d-none">
    </div>

    <p class="text-center fw-bold my-3">ó</p>

    <div class="mb-3">
      <label class="form-label">Enlace externo (YouTube / Drive / Vimeo / PDF seguro)</label>
      <input type="url" name="link" class="form-control" placeholder="https://youtube.com/...">
    </div>

    <button class="btn btn-success w-100">
      <i class="bi bi-cloud-arrow-up"></i> Guardar
    </button>
  </form>
</div>

<script>
const dropArea = document.getElementById("dropArea");
const fileInput = document.getElementById("fileInput");

dropArea.addEventListener("click", () => fileInput.click());

dropArea.addEventListener("dragover", e => {
  e.preventDefault();
  dropArea.classList.add("border-primary");
});

dropArea.addEventListener("dragleave", () => dropArea.classList.remove("border-primary"));

dropArea.addEventListener("drop", e => {
  e.preventDefault();
  dropArea.classList.remove("border-primary");
  fileInput.files = e.dataTransfer.files;
});

document.getElementById("uploadForm").onsubmit = async function(e){
  e.preventDefault();
  let form = new FormData(this);
  
  let res = await fetch("api/upload.php", { method:"POST", body:form });
  let json = await res.json();

  if(json.ok){
    alert("Material registrado ✅");
    location.href="index.php";
  } else {
    alert("Error: " + json.error);
  }
}
</script>

<?php require_once "../../includes/footerfiltrodatos.php"; ?>
