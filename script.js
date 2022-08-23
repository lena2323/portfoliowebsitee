// Hamburger menu
const navUL = document.getElementById('nav-ul');
const navLink1 = document.getElementById('nav-link1');
const navLink2 = document.getElementById('nav-link2');
const navLink3 = document.getElementById('nav-link3');
const navLink4 = document.getElementById('nav-link4');
const navlinkExp = document.getElementById('nav-link-Exp');
const hamburger = document.getElementById("hamburger");
const navBar = document.getElementById("navbar");

hamburger.addEventListener('click',() =>{
  hamburger.classList.toggle('notActive');
  navUL.classList.toggle('show');
  navBar.classList.toggle('show');
  navLink1.classList.add('animationForNavInHamburgerMenu');
  navLink2.classList.add('animationForNavInHamburgerMenu');
  navLink3.classList.add('animationForNavInHamburgerMenu');
  navLink4.classList.add('animationForNavInHamburgerMenu');
  navlinkExp.classList.add('animationForNavInHamburgerMenu');
});

// Hamburger to open and close on link click

navLink1.addEventListener('click',() =>{
  navUL.classList.remove("show");
  hamburger.classList.add('notActive');
  navBar.classList.remove('show');
});

navLink2.addEventListener('click',() =>{
  navUL.classList.remove("show");
  hamburger.classList.add('notActive');
  navBar.classList.remove('show');
});

navLink3.addEventListener('click',() =>{
  navUL.classList.remove("show");
  hamburger.classList.add('notActive');
  navBar.classList.remove('show');
});

navLink4.addEventListener('click',() =>{
  navUL.classList.remove("show");
  hamburger.classList.add('notActive');
  navBar.classList.remove('show');
});

navlinkExp.addEventListener('click',() =>{
  navUL.classList.remove("show");
  hamburger.classList.add('notActive');
  navBar.classList.remove('show');
});

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