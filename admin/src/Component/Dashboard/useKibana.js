import { useEffect, useState } from "react"
import { useTranslate } from 'react-admin';

const useKibana = () => {
    const [ status, setStatus]  = useState(false)    // Etat de la connexion à Kibana
    const [ error, setError]    = useState('')    // Etat de la connexion à Kibana
    const translate = useTranslate()

    useEffect( () => {
        const token         = localStorage.getItem('token')
        const instanceName  = process.env.REACT_APP_SCOPE_INSTANCE_NAME
        const kibanaAuthenticationApi = process.env.REACT_APP_KIBANA_URL +'/login/' + instanceName
        console.log("Scope API:", kibanaAuthenticationApi)

        const getKibanaCookie = async () => {
             fetch(kibanaAuthenticationApi, {
                credentials: 'include',
                headers: new Headers({ 'Authorization':`Bearer ${token}`}),
                method: "GET",
            })
            .then( reponse => {
                console.log(reponse)
                // Should check if cookie is there
                if (reponse.status === 200 ) {
                    setStatus(true)
                } else {
                    setStatus(false)
                    setError(translate('custom.dashboard.kibanaAuthenticationApiReturnSomethingWrong'))
                }
            })
            .catch( error => {
                console.log("Ereur lors de la connexion à Kibana :", error)
                setStatus(false)
                setError(translate('custom.dashboard.kibanaAuthenticationApiFetchError'))
            })
        }
        if (token && instanceName && kibanaAuthenticationApi) {
            getKibanaCookie()
        }
        
    },[]
    )

    return [status, error]
}

export { useKibana }



