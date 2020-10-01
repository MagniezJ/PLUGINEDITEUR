
console.log("HELLO");

const art=document.querySelectorAll('.article'); //recuperer bouton modifier

const tag=document.querySelector('#ta');
const cate=document.querySelector('#cat');
const img=document.querySelector('#img');

const content=document.querySelector('#content');
const id=document.querySelector('.post');
const modid=document.querySelector('button');

const modifier=document.querySelectorAll('.mod');
console.log("gg");
modifier.forEach((element,index) => {
    const title=document.getElementById(`title${index}`);
    const auteur=document.getElementById(`aut${index}`);
    const content=document.getElementById(`content${index}`);
    const imgd=document.getElementById(`img${index}`);
    element.addEventListener('click',()=>{
        
        imgd.style.display="block";
        console.log(imgd.innerHTML);
        
        document.getElementById('tb').style.display="none";
        document.getElementById('fom').style.display="block";
        document.getElementById('name').value=auteur.innerText;
        document.getElementById('titre').value=title.innerText;
        document.getElementById('hello').innerText=content.innerText;
        document.getElementById('modifimg').style.display="block";
        document.getElementById('text').style.display="none";
        
        document.getElementById('modifimg').src=imgd.innerHTML;
        
       /*  
/*  */
    })});
