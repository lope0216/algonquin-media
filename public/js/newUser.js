function clearForm() {
    // Get the form element
    const form = document.querySelector("form");

    // Reset the form fields
    form.reset();

    // Clear error messages
    const errorMessages = document.querySelectorAll(".text-danger");
    errorMessages.forEach((error) => {
        error.textContent = "";
    });
}
