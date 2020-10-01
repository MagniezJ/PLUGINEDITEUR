
console.log("HELLO");

const art=document.querySelectorAll('.article'); //recuperer bouton modifier

const tag=document.querySelector('#ta');
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
        const d=document.getElementById(`img${index}`).innerHTML;
        document.getElementById('tb').style.display="none";
        document.getElementById('fom').style.display="block";
        document.getElementById('name').value=auteur.innerText;
        document.getElementById('titre').value=title.innerText;
        document.getElementById('hello').innerText=content.innerText;
        document.getElementById('modifimg').style.display="block";
        document.getElementById('text').style.display="none";
        document.getElementById('modifimg').src=d;
        const cate=document.getElementById(`cat${index}`).innerText;
        const tage=document.getElementById(`ta${index}`).innerText;
        ///////////

       
        const cat=document.querySelectorAll('.caté');
        cat.forEach((element) => {
            if(" "+element.id == cate){
                element.checked=true;
            }
        });
        const tag=document.querySelectorAll('.tage');
        
         console.log(tage);
        tag.forEach((element)=>{
            console.log(" "+element.id);
            console.log(tage)
            if(" "+element.id == tage){
                element.checked=true;
                console.log("BRAVO");
            }
            else{
                console.log("nul");
            } 

         }
            ) ;
    })
});