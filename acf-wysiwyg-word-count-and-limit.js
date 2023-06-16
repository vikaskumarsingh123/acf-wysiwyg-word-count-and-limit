/* eslint-disable prettier/prettier */

//Use ACF JS API to set our function to run when a tinymce/wysiwyg is intialized:
if( 'acf' in window ){ //only do this if ACF plugin is active on this posttype
    acf.addAction('wysiwyg_tinymce_init', function( ed, id, mceInit, field ){
        addCounterToWysiwygField(field);
    });
}

/**
 * Adds an element to display the count of words to the DOM for the TinyMCE related to the supplied param.
 * Also adds a word counting function to that element
 * @param {object} acf_wysiwyg_field - The field returned by acf javascript api.
 */
function addCounterToWysiwygField( acf_wysiwyg_field ){
    acf_wysiwyg_field.max_words = acf_wysiwyg_field.$el[0].querySelector("span[data-maxwords]").dataset.maxwords;
    var the_text_editor = document.getElementById(acf_wysiwyg_field.data.id);
    var the_statusbar = acf_wysiwyg_field.$el[0].querySelector('.mce-statusbar');
    acf_wysiwyg_field.word_counter = document.createElement('span');

    acf_wysiwyg_field.word_counter.updateCharCount = function(){
        var words = the_text_editor.value.replace(/(<([^>]+)>)/gi, '');
        var words_count = words.trim().replace(/\s+/gi, ' ').split(' ').length;
        acf_wysiwyg_field.word_counter.wordsCount = words_count;
        
        if( parseInt(acf_wysiwyg_field.max_words) > 0 ){ //if this is set to 0, it means no word limit
            acf_wysiwyg_field.word_counter.innerHTML =  words_count + ' word(s). Max words allowed: ' + acf_wysiwyg_field.max_words;
            if(words_count > acf_wysiwyg_field.max_words){
                acf_wysiwyg_field.word_counter.style.color = 'red'
            } else {
                acf_wysiwyg_field.word_counter.style.color = 'unset'
            }
        } else acf_wysiwyg_field.word_counter.innerHTML =  words_count + ' word(s)';
        
    };

    the_statusbar.appendChild(acf_wysiwyg_field.word_counter);
    //update initial character count upon init:
        acf_wysiwyg_field.word_counter.updateCharCount();
    //make it update on every keyup, change, blur, and focus
        tinymce.get(acf_wysiwyg_field.data.id).on('input', acf_wysiwyg_field.word_counter.updateCharCount);
}
