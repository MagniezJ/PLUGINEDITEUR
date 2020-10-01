

const modifier=document.querySelectorAll('.mod'); //recuperation de tout les boutons modifier

modifier.forEach((element,index) => { //boucles pour tout les boutons
    const title=document.getElementById(`title${index}`); //recuperation du titre selon articles
    const auteur=document.getElementById(`aut${index}`); //recuperation de l'auteur selon articles
    const content=document.getElementById(`content${index}`); //recuperation du contenu selon articles
    const imgd=document.getElementById(`img${index}`); //recuperation de l'image mise en avant selon articles
    element.addEventListener('click',()=>{  //event si bouton modifier cliquer
        imgd.style.display="block"; //affichage de l'image
        const d=document.getElementById(`img${index}`).innerHTML; //recuperation de l'innerHTML de l'image a afficher
        document.getElementById('tb').style.display="none"; // fait disparaitre liste
        document.getElementById('fom').style.display="block"; //fait apparaitre formulaire
        document.getElementById('name').value=auteur.innerText; // injecte nom auteur
        document.getElementById('titre').value=title.innerText; //injecte titre
        document.getElementById('hello').innerText=content.innerText; //injecte content //PB IMG dans content
        document.getElementById('modifimg').style.display="block"; //affiche image dans formulaire
        document.getElementById('text').style.display="none"; // supprime text de image preview
        document.getElementById('modifimg').src=d;  //injection image
        /* const tage=document.getElementById(`ta${index}`).innerText; */ //recuperation tags a injecter
        /* const tag=document.querySelectorAll('.tage');  */// recuperation tags en attente d injection 
        /* tag.forEach((element) => { // PB de if // boucle pour chaque tags
            console.log(" "+element.id);
            console.log(tage);
            if(" "+element.id == tage){ //si id correspond au tag recuperer
                element.checked=true; //check le tag en attente
                console.log("BRAVO");
            }
            else{
                console.log("try again");
            }
        }); */
        const cate=document.getElementById(`cat${index}`).innerText; //recup categorie a injecter
        const cat=document.querySelectorAll('.caté'); //recup categorie en attente d injection
        cat.forEach((element) => { //pour chaque categorie 
            if(" "+element.id == cate){ // si Id correspon au nom de catégorie
                element.checked=true; //je check la categorie
            }
        })
    })
});
