//inicio autenticacion//

document.getElementById('loginForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    console.log('Email:', email, 'Password:', password);
    // Aquí puedes agregar llamadas a API para manejar la autenticación
    alert('Form submitted');
});
//End autenticacion//

//inicio carga imagen previsualizacion form

document.getElementById('imageUpload').addEventListener('change', function(event) {
    var output = document.getElementById('imagePreview');
    output.innerHTML = ''; // Clear previous previews
    if (this.files && this.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            var imgElement = document.createElement('img');
            imgElement.style.maxWidth = '200px'; // Set the size of the preview image
            imgElement.style.maxHeight = '200px';
            imgElement.src = e.target.result;
            output.appendChild(imgElement);
        }
        reader.readAsDataURL(this.files[0]);
    }
});

//end carga visualizacion//