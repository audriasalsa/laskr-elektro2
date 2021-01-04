function VerifikasiAbstractIndex()
{
    this._textAreaId = 'abstrak';
    this._editor = null;
}

VerifikasiAbstractIndex.prototype.main = function () {

    let textArea = document.getElementById(this._textAreaId);

    tinymce.init({ selector:'textarea' });
};

(new VerifikasiAbstractIndex()).main();