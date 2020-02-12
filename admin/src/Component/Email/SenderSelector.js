import React, { useEffect, useState } from 'react';
import { useDataProvider } from 'react-admin';
import {RadioGroup, FormControlLabel, Radio} from '@material-ui/core';

const SenderSelector = ({onExpediteurChange}) => {
    const expediteurParDefaut = ({replyTo:process.env.REACT_APP_SENDER_EMAIL_DEFAULT, fromName:process.env.REACT_APP_SENDER_FROM_NAME_DEFAULT})  // Should be in config file
    const [expediteurs, setExpediteurs] = useState([]); 
    const [ choix, setChoix] = useState(0)
    const dataProvider      = useDataProvider();
    
    useEffect( () => {  localStorage.getItem('id') && 
                        dataProvider.getOne('users',{id: "/users/"+localStorage.getItem('id')} )
                            .then( ({ data }) => {
                                const expediteurConnecte = ({replyTo:data.email, fromName: data.givenName + " " + data.familyName, id:data.id})
                                setExpediteurs( [{...expediteurParDefaut, id:data.id}, expediteurConnecte] )
                                onExpediteurChange( {...expediteurParDefaut, id:data.id} )
                            })
                            .catch( error => {
                                setExpediteurs( [expediteurParDefaut] )
                                console.log("Erreur lors de la recherche de l'utilisateur courant :", error)
                            })
                        return
                    }
                , [])

    const changeChoix = e => {
        const nouveauChoix = parseInt(e.target.value)
        setChoix(nouveauChoix)
        onExpediteurChange(expediteurs[nouveauChoix])
    }

    if (expediteurs.length === 0) return <p>Chargement des expéditeurs...</p>

    return (
        <RadioGroup row aria-label="expediteur" name="expediteur" value={choix} onChange={changeChoix} > 
            { expediteurs.map( (expediteur, indice) => <FormControlLabel    key={indice} 
                                                                            value={indice} 
                                                                            control={<Radio />} 
                                                                            label={expediteur.fromName + "<" + expediteur.replyTo + ">"} 
                                                        /> ) 
            }
        </RadioGroup>

    )
}

export default SenderSelector

