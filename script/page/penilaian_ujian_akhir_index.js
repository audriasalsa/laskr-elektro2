function PenilaianUjianAkhirIndex()
{

}

PenilaianUjianAkhirIndex.btnSamakanNilai_onClick = function (formFields, groupCount)
{
    for(i = 1; i < groupCount; i++)
    {
        for(j = 0; j < formFields.length; j++)
        {
            let defaultId = formFields[j];
            let mainElement = document.getElementById(defaultId + '__0');
            let changedElement = document.getElementById(defaultId + '__' + i);
            changedElement.value = mainElement.value;
        }
    }

    alert("Semua penilaian disamakan dengan mahasiswa #1. Jangan lupa klik button 'Simpan Penilaian' untuk menerapkan perubahan.");
};