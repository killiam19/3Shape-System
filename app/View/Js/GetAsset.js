document.getElementById("assetname").addEventListener("blur", async function() {
    const assetname = this.value;

    if (!assetname) return;

    try {
        const response = await fetch(`/3Shape_project/Controller/check_activo.php?assetname=${assetname}`);
        const data = await response.json();

        console.log("Server Response:", data); // Debugging

        const message = document.getElementById("asset-message");
        const form = document.getElementById('registform');

        if (data.exists) {
            message.textContent = "This Asset is already registered.";
            message.style.color = "red";

            form.addEventListener('submit', preventFormSubmission);
        } else {
            message.textContent = "Current assets.";
            message.style.color = "green";

            form.removeEventListener('submit', preventFormSubmission);
        }
    } catch (error) {
        console.error("Error validating serial number:", error);
    }
});

function preventFormSubmission(event) {
    event.preventDefault();
    alert("The asset is already registered. Please enter a different asset name.");
}
