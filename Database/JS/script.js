function toggleSidebar() {
    // Select sidebar and content elements
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');

    // Toggle hidden class on the sidebar
    sidebar.classList.toggle('hidden');
    content.classList.toggle('sidebar-hidden');
}


