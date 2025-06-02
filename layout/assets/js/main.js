function switchPopupContainer(){
    document.addEventListener('click', function(e){
        if(e.target.classList.contains('popup__btn')){
            e.preventDefault();
            let switchTarget = e.target.getAttribute("href");
            document.querySelector('.popup__btn.active').classList.remove('active');
            document.querySelector('.popup__switch_block.active').classList.remove("active");
            document.querySelector(`.popup__switch_block[data-name="${switchTarget}"]`).classList.add("active");
            e.target.classList.add('active');
        }
    });
} switchPopupContainer();

function generalPopup(){
    document.addEventListener('click', function(e){
        if(e.target.hasAttribute('data-popup')){
            e.preventDefault();
            closeAllPopup();
            document.querySelector(`.popup[data-popup-name="${e.target.getAttribute('data-popup')}"]`).classList.add('active');
        }
        if(
            e.target.classList.contains('over') 
            ||
            e.target.classList.contains('close__popup_btn')
            ||
            e.target.parentNode.classList.contains('close__popup_btn')
            ||
            e.target.parentNode.parentNode.classList.contains('close__popup_btn')
            ||
            e.target.parentNode.parentNode.parentNode.classList.contains('close__popup_btn')
        ){
            closeAllPopup();
        }
    });
} generalPopup();
function closeAllPopup(){
    let popupArrActive = document.querySelectorAll('.popup.active');
    popupArrActive.forEach(element => {
        element.classList.remove('active');
    });
}