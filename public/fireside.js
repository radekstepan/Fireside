function reply(id) {
    if ($(id).style.display != 'none') {
        Effect.BlindUp(id, {
            duration: 0.2
        });
    } else {
        Effect.BlindDown(id, {
            duration: 0.3
        });
        setTimeout('$(\''+id+'\').focus()', 300);
    }
}

if (window.Widget == undefined) window.Widget = {};
Widget.Textarea = Class.create({
  initialize: function(textarea, options)
  {
    this.textarea = $(textarea);
    this.options = $H({
      'min_height' : 20,
      'max_length' : 99999
    }).update(options);

    this.textarea.observe('keyup', this.refresh.bind(this));

    this._shadow = new Element('div').setStyle({
      lineHeight : this.textarea.getStyle('lineHeight'),
      fontSize : this.textarea.getStyle('fontSize'),
      fontFamily : this.textarea.getStyle('fontFamily'),
      position : 'absolute',
      top: '-10000px',
      left: '-10000px',
      width: this.textarea.getWidth() + 'px'
    });
    this.textarea.insert({ after: this._shadow });

    //this._remainingCharacters = new Element('p').addClassName('remainingCharacters');
    this.textarea.insert({after: this._remainingCharacters});
    this.refresh();
  },

  refresh: function()
  {
    this._shadow.update($F(this.textarea).replace(/\n/g, '<br/>'));
    this.textarea.setStyle({
      height: Math.max(parseInt(this._shadow.getHeight()) + parseInt(this.textarea.getStyle('lineHeight').replace('px', '')), this.options.get('min_height')) + 'px'
    });
    // move the viewport
    if ($('scroll-'+this.textarea.id)) $('scroll-'+this.textarea.id).scrollTo();

    //var remaining = this.options.get('max_length') - $F(this.textarea).length;
    //this._remainingCharacters.update(Math.abs(remaining)  + ' characters ' + (remaining > 0 ? 'remaining' : 'over the limit'));
  }
});

function substrText(e, i) {
  var text = e.innerHTML;
  if (text.length > 85) {
      var hiddenText = '<span style="display:none;" id="more_' + i + '">' + text.substr(85) + '</span>';
      text = text.substr(0, 85);
      // crudely create new element...
      var toggler = '<div class="more" onclick="this.remove();moreText(\'more_' + i + '\');return false;">... (more)</div>';
      e.innerHTML = text + toggler + hiddenText;
  }
}

function moreText(id) {
    Effect.Appear(id, {duration: 0.3});
}