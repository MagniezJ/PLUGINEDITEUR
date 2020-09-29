console.log("HELLO");
const modifier=document.querySelector('.mod'); //recuperer bouton modifier
const auteur=document.querySelector('#aut');
const tag=document.querySelector('#ta');
const cate=document.querySelector('#cat');
const img=document.querySelector('#img');
const title=document.querySelector('#title');
const content=document.querySelector('#content');


modifier.addEventListener('click',()=>{ //event onclick
    document.getElementById('tb').style.display = "none";
    document.getElementById('fom').style.display = "block";
    document.getElementById('user-submitted-name').value=auteur.innerText;
    document.getElementById('user-submitted-title').value=title.innerText;
    document.getElementById('user-submitted-content').innerText=content.innerText;
    
    img.style.display="block";
    
    document.getElementById('modifimg').style.display="block";
    document.getElementById('imagePreview').innerHTML=img.innerHTML;
    console.log("reussie");

})