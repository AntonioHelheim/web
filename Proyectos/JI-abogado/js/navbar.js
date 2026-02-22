function initMobileMenu(){

  const openBtn = document.getElementById("openMenu");
  const closeBtn = document.getElementById("closeMenu");
  const panel = document.getElementById("mobilePanel");
  const links = document.querySelectorAll(".mobile-nav a");

  if(!panel) return;

  if(openBtn){
    openBtn.addEventListener("click", ()=>{
      panel.classList.add("show");
      document.body.style.overflow = "hidden";
    });
  }

  if(closeBtn){
    closeBtn.addEventListener("click", ()=>{
      panel.classList.remove("show");
      document.body.style.overflow = "";
    });
  }

  links.forEach(link=>{
    link.addEventListener("click", ()=>{
      panel.classList.remove("show");
      document.body.style.overflow = "";
    });
  });

}
