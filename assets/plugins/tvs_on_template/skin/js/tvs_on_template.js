/**
 * $Id: tvs_on_template.js 15 2009-10-07 21:00:33Z stefan $
 */

var Tvs_On_Template = new Class({
    options: {
        status : true
    },
    initialize: function(options){
        this.setOptions(options);
        this.status       = this.options.status;
        this.icons        = this.options.icons;
        this.templates    = this.options.templates;
        this.tv_container = $(this.options.tv_container);
        this.tvs          = this.tv_container.getElements('ul.category_elements li');
        this.setStatus();
        this.setTemplateIndicator();
        this.addTemplateEvent();
        this.addButtonEvent();
        this.setTvList();
    },
    save: function(status){
        var button   = this.options.savebutton;
        var img      = button.getElement('img');
        if( status !== false ) {
            switch( status ){
                case 'disable' :
                    this.status = false;
                    button.removeProperty('onclick');
                    img.setProperty('src', this.icons.error );
                    break;
                case 'enable' :
                    this.status = true;
                    button.setProperty('onclick', "documentDirty=false; document.mutate.save.click(); saveWait('mutate')" );
                    img.setProperty('src', this.icons.ok );
                    break;
            }
        }
    },
    setStatus: function() {
        var value =  this.options.input_template.getProperty('value').trim().toLowerCase();
        var current_template = this.options.current_template.trim().toLowerCase();
        
        if( value === '' || value !== current_template || this.templates.contains( value ) ) { 
            this.status = false;
            this.save('disable');
        }
        else {
            this.status = true;
            this.save('enable');
        }
    },
    setTemplateIndicator : function() {
        this.indicator = new Element('img',{
            'id'  : 'plugin-tt-indicator',
            'alt' : 'Check template',
            'src' : this.status ? this.icons.ok : this.icons.error
        }).injectAfter( this.options.input_template );
    },
    addTemplateEvent: function(){
        this.options.input_template.addEvent('keyup', function(){
            var value =  this.options.input_template.getProperty('value').trim().toLowerCase();
            if( this.templates.contains( value ) || value === '' ) {
                this.indicator.src = this.icons.error;
                this.save('disable');
                this.status = false;
            }
            else {
                this.indicator.src = this.icons.ok;
                this.save('enable');
                this.status = true;
            }
        }.bind(this));
    },
    addButtonEvent: function(){
        this.options.savebutton.addEvent('click', function(){
            if( this.status === false ) {
                this.options.template_effect.start({
                    'background-color' : ['#F66', '#FFF']
                    //'padding'          : ['5px','0']
                });
            }
        }.bind(this));
    },
    addTvEvent : function(){
        this.tvs.getElements('input[type=checkbox]').each( function( checkbox ) {
            checkbox.addEvent('change', function() {
                if( this.getProperty('checked') === true ){
                    this.getParent().addClass( 'checked' );
                }
                else {
                    this.getParent().removeClass( 'checked' );
                }
           });
        });
    },
    setTvRank : function(){
        this.tv_container.getElements('ul.category_elements li input.rank').each( function( input, i ) {
            input.setProperty( 'value', i );
        },this);
    },
    setTvList:function(e){
        if( $(this.options.tv_container) === null) {
            return false;
        }
        else {
            this.addTvEvent();
            this.setTvRank();
            this.tv_container.getElements('ul.category_elements').each( function( ul ) {
                // make the li-elements sortable
                new Sortables( ul, {
                    // add pluginclass as a option.
                    ttplugin   : this,
                    handles    : 'span.sort-elements-' + ul.getProperty('id'),
                    ghost      : false,
                    onStart    : function(element){
                        element.addClass('move');
                    },
                    onComplete : function(element){
                        element.removeClass('move');
                        // reorder the indexes / rank
                        // @TODO how to address the pluginclass from here?
                        this.options.ttplugin.setTvRank();
                        /*
                        this.list.getChildren().each(function(element, i){
                            element.getElement('input.rank').setProperty( 'value', (i+1) );
                        });
                        */
                    }
                });
            },this);
        }
    }
});

Tvs_On_Template.implement(new Events, new Options);
