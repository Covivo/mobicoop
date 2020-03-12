
import React, {useState, useEffect} from 'react'
import TextField from '@material-ui/core/TextField';
import { useInput, useNotify, useDataProvider } from 'react-admin';

const CurrentUserInput = props => {
    const {
        input: { name, onChange },
        meta: { touched, error },
        isRequired
    }                   = useInput(props)
    const notify        = useNotify()
    const dataProvider  = useDataProvider()
    const [currentUserName, setCurrentUserName] = useState("...")

    useEffect( () => {  localStorage.getItem('id') && 
                        dataProvider.getOne('users',{id: "/users/"+localStorage.getItem('id')} )
                            .then( ({ data }) => {
                                setCurrentUserName(data.givenName + " " + data.familyName)
                                onChange(data.id) 
                            })
                            .catch( error => {
                                notify("Erreur lors de la recherche de l'utilisateur courant", 'warning')
                            })
                        return
                    }
                , []
    )

    return (
        <TextField
            name={name}
            disabled
            value={currentUserName}
            label={props.label}
            error={!!(touched && error)}
            helperText={touched && error}
        />
    )
}

export default CurrentUserInput
