// Get DOM elements once at the start
const serialInput = document.getElementById("serial");
const form = document.getElementById('registform');
const message = document.getElementById("serial-message");

// Store the serial validation state
let isSerialValid = true;

// Add form submit listener immediately
form.addEventListener('submit', function(event) {
    if (!isSerialValid) {
        event.preventDefault();
        alert("The serial is already registered. Please use a different serial number.");
    }
});

// Serial input blur event
serialInput.addEventListener("blur", async function () {
    const serial = this.value;
    if (!serial) {
        isSerialValid = false;
        return;
    }

    try {
        const response = await fetch("/3Shape_project/Controller/check_serial.php?serial=" + encodeURIComponent(serial));
        const contentType = response.headers.get("content-type");

        if (!contentType || !contentType.includes("application/json")) {
            throw new Error("Invalid content type: " + contentType);
        }

        const data = await response.json();
        console.log("Server response:", data);

        if (data.exists) {
            message.textContent = "This Asset is already registered.";
            message.style.color = "red";
            isSerialValid = false;
        } else {
            message.textContent = "Available serial number.";
            message.style.color = "green";
            isSerialValid = true;
        }
    } catch (error) {
        console.error("Error validating serial number:", error);
        alert("Error validating serial number. Please try again.");
        isSerialValid = false;
    }
});
