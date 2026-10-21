(function(){
'use strict';

const shell=document.querySelector('.question-bank-shell');
if(!shell)return;

const csrf=shell.dataset.csrf||'';
const i18n=JSON.parse(document.getElementById('questionBankI18n').textContent||'{}');
const list=document.getElementById('questionBankList');
const search=document.getElementById('questionBankSearch');
const form=document.getElementById('questionBankForm');
const empty=document.getElementById('questionBankEmpty');
const alertBox=document.getElementById('questionBankAlert');
const idInput=document.getElementById('questionBankId');
const codeLabel=document.getElementById('questionBankCode');
const editorTitle=document.getElementById('questionBankEditorTitle');
const state=document.getElementById('questionBankState');
const newBtn=document.getElementById('questionBankNew');
let questions=[];
let selected=null;

function alertMessage(type,message){
    alertBox.className='alert alert-'+type;
    alertBox.textContent=message||'';
    alertBox.classList.toggle('d-none',!message);
}
function request(url,options){
    const opts=options||{};
    opts.credentials='same-origin';
    opts.headers=Object.assign({'Accept':'application/json'},opts.headers||{});
    return fetch(url,opts).then(r=>r.json().then(j=>{
        if(!r.ok||!j.success)throw new Error(j.message||i18n.error||'Error');
        return j.data||{};
    }));
}
function languagePanels(){
    return Array.from(form.querySelectorAll('[data-language-panel]'));
}
function blankQuestion(){
    const translations={};
    languagePanels().forEach(panel=>{
        translations[panel.dataset.languagePanel]={
            question:'',
            options:[0,1,2,3].map(i=>({text:'',sort_order:i+1}))
        };
    });
    return {id_question:null,question_code:'',state:1,translations:translations,options:[]};
}
function renderList(){
    const term=(search.value||'').trim().toLowerCase();
    list.innerHTML='';
    questions.filter(q=>{
        if(!term)return true;
        const es=(q.translations.es&&q.translations.es.question)||'';
        return q.question_code.toLowerCase().includes(term)||es.toLowerCase().includes(term);
    }).forEach(q=>{
        const btn=document.createElement('button');
        btn.type='button';
        btn.className='question-bank-list-item'+(selected&&selected.id_question===q.id_question?' is-active':'');
        btn.innerHTML='<strong>'+escapeHtml(q.question_code)+'</strong><span>'+escapeHtml((q.translations.es&&q.translations.es.question)||'')+'</span><small>'+(q.state?escapeHtml(i18n.active):escapeHtml(i18n.inactive))+'</small>';
        btn.addEventListener('click',()=>openQuestion(q));
        list.appendChild(btn);
    });
}
function openQuestion(q){
    selected=q;
    idInput.value=q.id_question||'';
    codeLabel.textContent=q.question_code||i18n.new_question||'';
    editorTitle.textContent=q.id_question?(i18n.edit_question||''):(i18n.new_question||'');
    state.checked=!!q.state;

    languagePanels().forEach(panel=>{
        const lang=panel.dataset.languagePanel;
        const data=q.translations[lang]||{question:'',options:[]};
        panel.querySelector('[data-question-text]').value=data.question||'';
        panel.querySelectorAll('[data-option-text]').forEach((input,index)=>{
            input.value=(data.options[index]&&data.options[index].text)||'';
        });
    });

    let correct=0;
    if(q.options&&q.options.length){
        const found=q.options.findIndex(o=>parseInt(o.is_correct,10)===1);
        if(found>=0)correct=found;
    }
    const radio=form.querySelector('input[name="correct_index"][value="'+correct+'"]');
    if(radio)radio.checked=true;

    empty.classList.add('d-none');
    form.classList.remove('d-none');
    renderList();
}
function collect(){
    const translations={};
    languagePanels().forEach(panel=>{
        const lang=panel.dataset.languagePanel;
        translations[lang]={
            question:panel.querySelector('[data-question-text]').value.trim(),
            options:Array.from(panel.querySelectorAll('[data-option-text]')).map((input,index)=>({
                text:input.value.trim(),
                sort_order:index+1
            }))
        };
    });
    return {
        csrf_token:csrf,
        id_question:idInput.value?parseInt(idInput.value,10):null,
        state:state.checked?1:0,
        correct_index:parseInt(form.querySelector('input[name="correct_index"]:checked').value,10),
        translations:translations
    };
}
function load(){
    list.innerHTML='<div class="p-3 text-muted">'+escapeHtml(i18n.loading||'')+'</div>';
    return request('listar.php').then(data=>{
        questions=data.questions||[];
        renderList();
        if(selected&&selected.id_question){
            const updated=questions.find(q=>q.id_question===selected.id_question);
            if(updated)openQuestion(updated);
        }
    }).catch(e=>alertMessage('danger',e.message));
}
function escapeHtml(v){
    return String(v).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
}

search.addEventListener('input',renderList);
newBtn.addEventListener('click',()=>openQuestion(blankQuestion()));

form.addEventListener('submit',function(event){
    event.preventDefault();
    const payload=collect();

    const complete=Object.values(payload.translations).every(lang=>
        lang.question&&lang.options.length===4&&lang.options.every(option=>option.text)
    );
    if(!complete){
        alertMessage('warning',i18n.all_languages_required||'');
        return;
    }

    const button=form.querySelector('button[type="submit"]');
    button.disabled=true;
    request('guardar.php',{
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify(payload)
    }).then(data=>{
        alertMessage('success',i18n.saved||'');
        selected={id_question:data.id_question};
        return load();
    }).catch(e=>alertMessage('danger',e.message))
      .finally(()=>{button.disabled=false;});
});

load();
})();
