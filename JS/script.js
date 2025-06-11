function toggleSidebar() {
    // Select sidebar and content elements
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');

    // Toggle hidden class on the sidebar
    sidebar.classList.toggle('hidden');
    content.classList.toggle('sidebar-hidden');
}

document.addEventListener("DOMContentLoaded", () => {
    if (typeof showPopup !== "undefined" && showPopup) {
        const modal = document.getElementById("successModal");
        if (modal) {
            modal.style.display = "flex";
        }
    }
});


document.addEventListener('DOMContentLoaded', () => {
  const modal = document.getElementById('deleteModal');
  const confirmBtn = document.getElementById('confirmDelete');
  const cancelBtn = document.getElementById('cancelDelete');
  let deleteId = null;

  // Open modal when clicking delete buttons
  document.querySelectorAll('.btn-delete').forEach(button => {
    button.addEventListener('click', e => {
      e.preventDefault();
      deleteId = button.getAttribute('data-id');
      modal.style.display = 'flex';
    });
  });

  // Cancel button closes modal
  cancelBtn.addEventListener('click', () => {
    modal.style.display = 'none';
    deleteId = null;
  });

  // Confirm delete button redirects to delete URL
  confirmBtn.addEventListener('click', () => {
    if (deleteId) {
      window.location.href = `?delete=${deleteId}`;
    }
  });

  // Optional: click outside modal content closes modal
  window.addEventListener('click', (e) => {
    if (e.target == modal) {
      modal.style.display = 'none';
      deleteId = null;
    }
  });
});









