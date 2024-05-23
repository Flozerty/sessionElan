// Pour recharger le script à chaque rechargement de la page
// document.addEventListener('DOMContentLoaded', (event) => {
// window.onload = function () { // marche pas :'(

const addButtons = document.querySelectorAll(".addButton");
const dialogAdd = document.querySelector(".dialogAdd");

const cancelButton = document.querySelectorAll(".cancel");
const body = document.querySelector("body");

const modifyButtons = document.querySelectorAll(".modifyButton");
const dialogModify = document.querySelector(".dialogModify");


addButtons.forEach(button => {
  button.addEventListener("click", () => {
    dialogAdd.showModal();
    openCheck(dialogAdd);
  });
});
modifyButtons.forEach(button => {
  button.addEventListener("click", () => {
    dialogModify.showModal();
    openCheck(dialogModify);
  });
});

cancelButton.forEach(button => {
  button.addEventListener("click", () => {

    if (dialogAdd?.open) {
      dialogAdd.close();
      openCheck(dialogAdd);
    }
    if (dialogModify?.open) {
      dialogModify.close();
      openCheck(dialogModify);
    }
  });
});

function openCheck(dialog) {
  if (dialog.open) {
    body.style.filter = "blur(4px)";
  } else {
    body.style.filter = "";
  }
}