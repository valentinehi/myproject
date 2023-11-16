let navbar = document.querySelector('.header .flex .navbar');
let menuBtn = document.querySelector('.header .flex #menu-btn');

menuBtn.onclick = () =>{
    menuBtn.classList.toggle('fa-times'); 
    navbar.classList.toggle('active');
}

window.onscroll = () =>{
    menuBtn.classList.remove('fa-times'); 
    navbar.classList.remove('active');
}

document.querySelectorAll('input[type="number"]').forEach(inputNumber => { 
    inputNumber.oninput = () =>{
        if(inputNumber.value.length > inputNumber.maxLength) inputNumber.value = inputNumber.value.slice(0, inputNumber.maxLength);
    }
});
