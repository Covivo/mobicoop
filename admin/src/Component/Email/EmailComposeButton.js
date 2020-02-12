import React, {Fragment, useEffect, useState} from 'react';
import MailIcon from '@material-ui/icons/Mail';
import { Button, useTranslate, useMutation,useDataProvider  } from 'react-admin';
import MailComposer from './MailComposer'
import RgpdConsent from './RgpdConsent'


const EmailComposeButton = ({ selectedIds, resource, basePath, filterValues }) => {
    const [open, setOpen] = useState(false);    // State of the mail modal
    const [openRgpd, setOpenRgpd] = useState(false);    // State of the RGPD modal
    const [rgpdAgree, setRgpdAgree] = useState(false);    // State of the RGPD modal
    const shouldFetch = !!Object.keys(filterValues).length;
    const [mutate, { data, error, loading, loaded }] = useMutation();
    const dataProvider      = useDataProvider();
    const [sender, setSender] = useState([]);
    const [campagneInit, setCampagneInit] = useState([]);
    const translate = useTranslate();

    useEffect( () => {  localStorage.getItem('id') &&
        dataProvider.getOne('users',{id: "/users/"+localStorage.getItem('id')} )
            .then( ({ data }) => {
                const senderConnecte = ({replyTo:data.email, fromName: data.givenName + " " + data.familyName, id:data.id})
                setSender( [ senderConnecte] )
            })
            .catch( error => {
                console.log("Erreur lors de la recherche de l'utilisateur courant :", error)
            })
            return
        }
        , [])
    const campaignCreateParameters = sender[0] ? {
        user    : sender[0].id,
        name    : process.env.REACT_APP_INIT_EMAIL_NAME,
        subject : process.env.REACT_APP_INIT_EMAIL_SUBJECT,

        fromName : sender[0].fromName,
        email   : sender[0].replyTo,
        replyTo : sender[0].replyTo,

        body    : JSON.stringify([]),
        status  : 0,
        medium  : "/media/2",   // media#2 is email

    } : {}
    const handleClick = () => {
        if (rgpdAgree) {
            mutate({
                type: 'create',
                resource: 'campaigns',
                payload: {
                    data: campaignCreateParameters
                },
            })
         }else{
            setOpenRgpd(true)
        }
    }
    useEffect(  () => {
        if (loaded && data.id) {
            setCampagneInit(data)
            setOpen(true);
        }
    }, [ data, loaded ])

    useEffect(  () => {
        if (rgpdAgree){
            handleClick()
        }
    }, [ rgpdAgree ])


    const selectedIdsFormat = selectedIds.map(x => x.replace('community_users', 'users'));


    return (
        <Fragment>
            <Button label={shouldFetch ? translate('custom.email.texte.emailTous')  : translate('custom.email.texte.emailSelect') } onClick={handleClick} startIcon={<MailIcon />} />
            { open && 
            <MailComposer
                isOpen={open}
                selectedIds={selectedIdsFormat}
                onClose={()=>setOpen(false)}
                shouldFetch={ shouldFetch }
                resource={resource}
                basePath={basePath}
                filterValues={filterValues}
                campagneInit = {campagneInit}

            />
            }
            <RgpdConsent
                isOpen={openRgpd}
                onClose={()=>setOpenRgpd(false)}
                iAgree={()=>setRgpdAgree(true)}
             />
        </Fragment>

    );
}

export default EmailComposeButton;