const progDuree = document.querySelectorAll('.progDuree')

progDuree.forEach(element => {
  const span = element.querySelector('.showDuree')
  const form = element.querySelector('.moduleForm')
  const editButton = element.querySelector('.editButton')

  editButton.addEventListener('click', () => {
    span.style.display = "none";
    form.style.display = "contents";
  })
});