const header = document.querySelector('header')
const nav = header.querySelector('nav')
const toggleMenu = header.querySelector('.toggle-menu')
const closeMenu = header.querySelector('.close-menu')

let menuOpen = false;
checkMenu()

toggleMenu.addEventListener('click', () => {
  nav.style.transform = 'translateX(0)';
  menuOpen = true;
  checkMenu()

})

closeMenu.addEventListener('click', () => {
  nav.style.transform = 'translateX(110%)';
  menuOpen = false;
  checkMenu()
})

// si on clique hors du header
document.addEventListener('click', (event) => {
  if (!nav.contains(event.target) && !toggleMenu.contains(event.target)) {
    nav.style.transform = 'translateX(110%)';
    menuOpen = false;
    checkMenu()
  }
})

function checkMenu() {
  if (menuOpen) {
    toggleMenu.style.display = "none"
    closeMenu.style.display = "contents"
  } else {
    toggleMenu.style.display = "contents"
    closeMenu.style.display = "none"
  }
}