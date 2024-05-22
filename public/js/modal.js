const addButtons = document.querySelectorAll(".addButton");
const cancelButton = document.getElementById("cancel");
const dialog = document.querySelector("dialog");
const body = document.querySelector("body");


addButtons.forEach(button => {
  button.addEventListener("click", () => {
    dialog.showModal();
    openCheck(dialog);
  });
});

cancelButton.addEventListener("click", () => {
  dialog.close();
  openCheck(dialog);
});

function openCheck(dialog) {
  if (dialog.open) {
    body.style.filter = "blur(4px)";
  } else {
    body.style.filter = "";
  }
}