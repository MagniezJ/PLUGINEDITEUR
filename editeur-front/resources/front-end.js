const inp=document.getElementById('thumbnail');
const pcontainer=document.getElementById('imagePreview');
const imgp=document.querySelector('.image-preview__image');
const text=document.querySelector('.image-preview__default-text');

inp.addEventListener('change',()=>{
    const file=document.getElementById('thumbnail').files[0];
    if (file){
        const reader=new FileReader();
        text.style.display="none";
        imgp.style.display="block";
        
        reader.addEventListener('load',()=>{ 
            imgp.setAttribute("src",reader.result);
        });
        reader.readAsDataURL(file);
    }
})