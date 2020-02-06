{"version":3,"sources":["text_field.js"],"names":["BX","namespace","isFunction","Landing","Utils","isBoolean","clone","bind","remove","escapeHtml","fireCustomEvent","UI","Field","Text","data","BaseField","apply","this","arguments","changeTagButton","onInputHandler","onInput","onValueChangeHandler","onValueChange","textOnly","content","input","innerHTML","onInputClick","onInputMousedown","onDocumentMouseup","onInputInput","onDocumentClick","onDocumentKeydown","onInputKeydown","document","prototype","constructor","__proto__","innerText","getValue","event","keyCode","isEditable","currentField","Panel","EditorPanel","getInstance","hide","disableEdit","isTextOnly","preventDefault","enableTextOnly","util","trim","disableTextOnly","fromInput","setTimeout","stopPropagation","enableEdit","Tool","ColorPicker","hideAll","Button","FontAction","requestAnimationFrame","target","nodeName","range","createRange","selectNode","window","getSelection","removeAllRanges","addRange","onChangeHandler","onChangeTag","show","layout","contentEditable","focus","value","tag","isContentEditable","reset","setValue","adjustTags","element","lastChild"],"mappings":"CAAC,WACA,aAEAA,GAAGC,UAAU,uBAEb,IAAIC,EAAaF,GAAGG,QAAQC,MAAMF,WAClC,IAAIG,EAAYL,GAAGG,QAAQC,MAAMC,UACjC,IAAIC,EAAQN,GAAGG,QAAQC,MAAME,MAC7B,IAAIC,EAAOP,GAAGG,QAAQC,MAAMG,KAC5B,IAAIC,EAASR,GAAGG,QAAQC,MAAMI,OAC9B,IAAIC,EAAaT,GAAGG,QAAQC,MAAMK,WAClC,IAAIC,EAAkBV,GAAGG,QAAQC,MAAMM,gBAUvCV,GAAGG,QAAQQ,GAAGC,MAAMC,KAAO,SAASC,GAEnCd,GAAGG,QAAQQ,GAAGC,MAAMG,UAAUC,MAAMC,KAAMC,WAG1CD,KAAKV,KAAOO,EAAKP,KAGjBU,KAAKE,gBAAkBL,EAAKK,gBAG5BF,KAAKG,eAAiBlB,EAAWY,EAAKO,SAAWP,EAAKO,QAAU,aAChEJ,KAAKK,qBAAuBpB,EAAWY,EAAKS,eAAiBT,EAAKS,cAAgB,aAGlFN,KAAKO,SAAWnB,EAAUS,EAAKU,UAAYV,EAAKU,SAAW,MAC3DP,KAAKQ,QAAUR,KAAKO,SAAWf,EAAWQ,KAAKQ,SAAWR,KAAKQ,QAC/DR,KAAKS,MAAMC,UAAYV,KAAKQ,QAG5BR,KAAKW,aAAeX,KAAKW,aAAarB,KAAKU,MAC3CA,KAAKY,iBAAmBZ,KAAKY,iBAAiBtB,KAAKU,MACnDA,KAAKa,kBAAoBb,KAAKa,kBAAkBvB,KAAKU,MACrDA,KAAKc,aAAed,KAAKc,aAAaxB,KAAKU,MAC3CA,KAAKe,gBAAkBf,KAAKe,gBAAgBzB,KAAKU,MACjDA,KAAKgB,kBAAoBhB,KAAKgB,kBAAkB1B,KAAKU,MACrDA,KAAKiB,eAAiBjB,KAAKiB,eAAe3B,KAAKU,MAG/CV,EAAKU,KAAKS,MAAO,QAAST,KAAKW,cAC/BrB,EAAKU,KAAKS,MAAO,YAAaT,KAAKY,kBACnCtB,EAAKU,KAAKS,MAAO,QAAST,KAAKc,cAC/BxB,EAAKU,KAAKS,MAAO,UAAWT,KAAKiB,gBAGjC3B,EAAK4B,SAAU,QAASlB,KAAKe,iBAC7BzB,EAAK4B,SAAU,UAAWlB,KAAKgB,mBAC/B1B,EAAK4B,SAAU,UAAWlB,KAAKa,oBAIhC9B,GAAGG,QAAQQ,GAAGC,MAAMC,KAAKuB,WACxBC,YAAarC,GAAGG,QAAQQ,GAAGC,MAAMC,KACjCyB,UAAWtC,GAAGG,QAAQQ,GAAGC,MAAMG,UAAUqB,UAIzCL,aAAc,WAEbd,KAAKG,eAAeH,KAAKS,MAAMa,WAC/BtB,KAAKK,qBAAqBL,MAE1BP,EAAgBO,KAAM,8BAA+BA,KAAKuB,cAQ3DP,kBAAmB,SAASQ,GAK3B,GAAIA,EAAMC,UAAY,GACtB,CACC,GAAIzB,KAAK0B,aACT,CACC,GAAI1B,OAASjB,GAAGG,QAAQQ,GAAGC,MAAMG,UAAU6B,aAC3C,CACC5C,GAAGG,QAAQQ,GAAGkC,MAAMC,YAAYC,cAAcC,OAG/C/B,KAAKgC,iBAMRf,eAAgB,SAASO,GAKxB,GAAIA,EAAMC,UAAY,GACtB,CACC,GAAIzB,KAAKiC,aACT,CACCT,EAAMU,oBASTC,eAAgB,WAEfnC,KAAKO,SAAW,KAChBP,KAAKS,MAAMC,UAAY3B,GAAGqD,KAAKC,KAAKrC,KAAKS,MAAMa,YAOhDgB,gBAAiB,WAEhBtC,KAAKO,SAAW,OAQjB0B,WAAY,WAEX,OAAOjC,KAAKO,UAObQ,gBAAiB,WAEhB,GAAIf,KAAK0B,eAAiB1B,KAAKuC,UAC/B,CACC,GAAIvC,OAASjB,GAAGG,QAAQQ,GAAGC,MAAMG,UAAU6B,aAC3C,CACC5C,GAAGG,QAAQQ,GAAGkC,MAAMC,YAAYC,cAAcC,OAG/C/B,KAAKgC,cAGNhC,KAAKuC,UAAY,OAIlB1B,kBAAmB,WAElB2B,WAAW,WACVxC,KAAKuC,UAAY,OAChBjD,KAAKU,MAAO,KAQfW,aAAc,SAASa,GAEtBA,EAAMU,iBACNV,EAAMiB,kBACNzC,KAAKuC,UAAY,OAIlB3B,iBAAkB,SAASY,GAE1BxB,KAAK0C,aAEL3D,GAAGG,QAAQQ,GAAGiD,KAAKC,YAAYC,UAC/B9D,GAAGG,QAAQQ,GAAGoD,OAAOC,WAAWF,UAEhCG,sBAAsB,WACrB,GAAIxB,EAAMyB,OAAOC,WAAa,IAC9B,CACC,IAAIC,EAAQjC,SAASkC,cACrBD,EAAME,WAAW7B,EAAMyB,QACvBK,OAAOC,eAAeC,kBACtBF,OAAOC,eAAeE,SAASN,MAIjCnD,KAAKuC,UAAY,KAEjBf,EAAMiB,mBAOPC,WAAY,WAEX,IAAK1C,KAAK0B,aACV,CACC,GAAI1B,OAASjB,GAAGG,QAAQQ,GAAGC,MAAMG,UAAU6B,cAAgB5C,GAAGG,QAAQQ,GAAGC,MAAMG,UAAU6B,eAAiB,KAC1G,CAEC5C,GAAGG,QAAQQ,GAAGC,MAAMG,UAAU6B,aAAaK,cAI5CjD,GAAGG,QAAQQ,GAAGC,MAAMG,UAAU6B,aAAe3B,KAG7C,IAAKA,KAAKiC,aACV,CACC,GAAIjC,KAAKE,gBACT,CACCF,KAAKE,gBAAgBwD,gBAAkB1D,KAAK2D,YAAYrE,KAAKU,MAG9DjB,GAAGG,QAAQQ,GAAGkC,MAAMC,YAAYC,cAAc8B,KAAK5D,KAAK6D,OAAQ,KAAM7D,KAAKE,iBAAmBF,KAAKE,iBAAmB,MACtHF,KAAKS,MAAMqD,gBAAkB,SAG9B,CACC/E,GAAGG,QAAQQ,GAAGkC,MAAMC,YAAYC,cAAcC,OAC9C/B,KAAKS,MAAMqD,gBAAkB,KAI9B9D,KAAKS,MAAMsD,UAKbJ,YAAa,SAASK,GAErBhE,KAAKiE,IAAMD,GAOZhC,YAAa,WAEZhC,KAAKS,MAAMqD,gBAAkB,OAQ9BpC,WAAY,WAEX,OAAO1B,KAAKS,MAAMyD,mBAInBC,MAAO,WAENnE,KAAKoE,SAAS,KAQfC,WAAY,SAASC,GAEpB,GAAIA,EAAQC,WAAaD,EAAQC,UAAUrB,WAAa,KACxD,CACC3D,EAAO+E,EAAQC,WACfvE,KAAKqE,WAAWC,GAGjB,OAAOA,GAIR/C,SAAU,WAET,OAAOvB,KAAKqE,WAAWhF,EAAMW,KAAKS,QAAQC,aAvS5C","file":""}