document.addEventListener('DOMContentLoaded', function () {
    const role = document.querySelector('.role-badge')?.textContent;

    if (role === 'Admin') {
        document.querySelector('.admin-section')?.style.setProperty('display', 'block');
    }
    if (role === 'Lehrer') {
        document.querySelector('.teacher-section')?.style.setProperty('display', 'block');
    }
});
