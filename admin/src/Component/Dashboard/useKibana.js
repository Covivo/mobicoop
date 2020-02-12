import { useEffect, useState } from "react"

/* ********************************************************************
For authentication:

L'accès à l'API requiert xpack.security
await fetch(`${url}/api/security/v1/login`, {
    body: JSON.stringify({
        username,
        password,
    }),
    credentials: 'include',
    headers: {
        "kbn-xsrf":"reporting"
    },
    method: "POST",
})

/!\ Note the credentials: 'include' setting of the request, which was crucial for getting the browser to persist the sid cookie.
https://discuss.elastic.co/t/logging-into-kibana-from-a-react-page-using-api-security-v1-login/95018/4

*/


const useKibana = ({username, password, url}) => {
    const [ status, setStatus] = useState(false)    // Etat de la connexion à Kibana
    useEffect( () => {
        const getKibanaCookie = async (username, password) => {
             fetch(`${url}/api/security/v1/login`, {
                 body: JSON.stringify({
                     username,
                     password,
                 }),
                credentials: 'include',
                headers: new Headers({
                    "kbn-version": "5.5.1",
                    "kbn-xsrf":"reporting",
                    "access-control-allow-origin'": "*",
                    'Content-Type': 'application/json'
                }),
                method: "POST",
            })
            .then( reponse => {
                console.log(reponse)
                // Should check if cookie is there
                if (Response.status === 200 ) setStatus(true)
            })
            .catch( error => {
                console.log("Ereur lors de la connexion à Kibana :", error)
                setStatus(false)
            })
        }
        getKibanaCookie(username, password)
    },[username, password, url ]
    )

    return status
}

export { useKibana }



