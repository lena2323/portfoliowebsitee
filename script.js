// Hamburger menu

const hamburger = document.getElementById('hamburger');
const navUL = document.getElementById('nav-ul');
const navLink1 = document.getElementById('nav-link1');
const navLink2 = document.getElementById('nav-link2');
const navLink3 = document.getElementById('nav-link3');


hamburger.addEventListener('click',() =>{
    navUL.classList.toggle('show');
});


/*const hamburger = document.getElementById("hamburger");
const hamburgerContainer = document.getElementById("hamburgerContainer");


const navBar = document.getElementById("nav-bar");


hamburger.addEventListener('click',() =>{
  hamburger.classList.toggle('notActive');
  navBar.classList.toggle('show');
});*/

// Hamburger to open and close on link click


navLink1.addEventListener('click',() =>{
    navUL.classList.remove("show");

});

navLink2.addEventListener('click',() =>{
  navUL.classList.remove("show");

});

navLink3.addEventListener('click',() =>{
  navUL.classList.remove("show");

});


/*******************************************/


/*********Animations oon scroll!**/

const scrollElements = document.querySelectorAll(".js-scroll");

const elementInView = (el, dividend = 1) => {
  const elementTop = el.getBoundingClientRect().top;

  return (
    elementTop <=
    (window.innerHeight || document.documentElement.clientHeight) / dividend
  );
};

const elementOutofView = (el) => {
  const elementTop = el.getBoundingClientRect().top;

  return (
    elementTop > (window.innerHeight || document.documentElement.clientHeight)
  );
};

const displayScrollElement = (element) => {
  element.classList.add("scrolled");
};

const hideScrollElement = (element) => {
  element.classList.remove("scrolled");
};

const handleScrollAnimation = () => {
  scrollElements.forEach((el) => {
    if (elementInView(el, 1.25)) {
      displayScrollElement(el);
    } else if (elementOutofView(el)) {
      hideScrollElement(el)
    }
  })
}

window.addEventListener("scroll", () => { 
  handleScrollAnimation();
});


  /********* Animations oon scroll! *******/
