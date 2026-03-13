document.addEventListener("DOMContentLoaded",()=>{

/* SCROLL SUAVE */

document.querySelectorAll('a[href^="#"]').forEach(anchor=>{

anchor.addEventListener("click",function(e){

const target=document.querySelector(this.getAttribute("href"))

if(target){

e.preventDefault()

window.scrollTo({
top:target.offsetTop-70,
behavior:"smooth"
})

}

})

})

/* BOTÓN SCROLL TOP */

const btn=document.getElementById("backToTop")

if(btn){

window.addEventListener("scroll",()=>{

if(window.scrollY>300){

btn.classList.add("visible")

}else{

btn.classList.remove("visible")

}

})

btn.addEventListener("click",()=>{

window.scrollTo({
top:0,
behavior:"smooth"
})

})

}

})