// import Swal from 'sweetalert2';

document.addEventListener('DOMContentLoaded', () => {
  const deleteButtons = document.querySelectorAll('.delete-btn');
  const saveButtons = document.querySelectorAll('.save-btn');
  deleteButtons.forEach(button => {
    button.addEventListener('click', onDeleteAlbum);
  });
  saveButtons.forEach(button => {
    button.addEventListener('click', onSaveAlbum);
  });
});


function onDeleteAlbum(event) {
  const row = this.closest('tr');
  const albumId = row.dataset.albumId;
  const albumTitle = row.dataset.albumTitle;
  const msgTitle = `Delete album ${albumTitle}?`;
  const msg = `All pictures will be deleted. \n This can not be undone.`;

  event.stopPropagation();
  event.preventDefault();

  Swal.fire({
    title: msgTitle,
    text: msg,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#dc3545",
    cancelButtonColor: "#6c757d",
    confirmButtonText: "Delete"
  }).then((result) => {
    if (result.isConfirmed) {
      document.getElementById('album_id').value = albumId;
      document.getElementById('submitType').value = 'delete';
      this.closest('form').submit();
    }
  });
}

function onSaveAlbum(event) {
  event.stopPropagation();
  event.preventDefault();

  const row = this.closest('tr');
  const albumId = row.dataset.albumId;
  const mode = row.querySelector('select').value;
  document.getElementById('album_id').value = albumId;
  document.getElementById('accessibility').value = mode;
  document.getElementById('submitType').value = 'save';
  this.closest('form').submit();
}