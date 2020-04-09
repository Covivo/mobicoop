import { useEffect, useState } from "react"

const useKibana = () => {
    const [ status, setStatus] = useState(false)    // Etat de la connexion à Kibana
    
    useEffect( () => {
        const token         = localStorage.getItem('token')
        const instanceName  = process.env.REACT_APP_INSTANCE_NAME
        const kibanaAuthenticationApi = process.env.REACT_APP_KIBANA_URL +'/login/' + instanceName
        console.log("kibanaAuthenticationApi:", kibanaAuthenticationApi)

        const getKibanaCookie = async () => {
             fetch(kibanaAuthenticationApi, {
                credentials: 'include',
                headers: new Headers({ 'Authorization':`Bearer ${token}`}),
                method: "GET",
            })
            .then( reponse => {
                console.log(reponse)
                // Should check if cookie is there
                if (reponse.status === 200 ) setStatus(true)
            })
            .catch( error => {
                console.log("Ereur lors de la connexion à Kibana :", error)
                setStatus(false)
            })
        }
        if (token && instanceName && kibanaAuthenticationApi) {
            getKibanaCookie()
        }
        
    },[]
    )

    return status
}

export { useKibana }



