(function(){

const VERSION="2026-03-13-1600"

async function loadComponent(el){

const url=el.dataset.include

try{

const res=await fetch(url+"?v="+VERSION)

if(!res.ok) throw new Error(res.status)

const html=await res.text()

el.innerHTML=html

}catch(err){

console.error("Error cargando componente:",url,err)

}

}

function init(){

const elements=document.querySelectorAll("[data-include]")

const observer=new IntersectionObserver(entries=>{

entries.forEach(entry=>{

if(entry.isIntersecting){

loadComponent(entry.target)
observer.unobserve(entry.target)

}

})

},{rootMargin:"200px"})

elements.forEach(el=>observer.observe(el))

}

document.addEventListener("DOMContentLoaded",init)

})()