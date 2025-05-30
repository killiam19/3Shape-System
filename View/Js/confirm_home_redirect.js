// ../View/Js/confirm_home_redirect.js
function confirmHomeRedirect() {
    Swal.fire({
        title: 'Are you sure?',
        text: "Do you want to go to the home page?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6d6d6d',
        confirmButtonText: 'Yes, go to home!'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '../index.php';
        }
    });
}