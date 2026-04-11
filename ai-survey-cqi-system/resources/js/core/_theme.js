const html = document.documentElement
const toggle = document.getElementById('themeToggle')

const savedTheme = localStorage.getItem('theme')

if(savedTheme){
    html.setAttribute('data-bs-theme', savedTheme)
    toggle.checked = savedTheme === 'dark'
}

toggle?.addEventListener('change', () => {

    const theme = toggle.checked ? 'dark' : 'light'

    html.setAttribute('data-bs-theme', theme)

    localStorage.setItem('theme', theme)

})