import React, { useState , useReducer, useEffect} from 'react';
import { useDataProvider,fetchUtils, fetchStart, fetchEnd,useUnselectAll } from 'react-admin';
import { useDispatch } from 'react-redux';
import {Modal, Grid, Button, TextField, Paper, CircularProgress, Fab} from '@material-ui/core';
import { makeStyles } from '@material-ui/core/styles';
import RichTextInput from './RichTextInput';
import ImageUpload from './ImageUpload';
import ArrowUpwardIcon from '@material-ui/icons/ArrowUpward';
import ArrowDownwardIcon from '@material-ui/icons/ArrowDownward';
import CloseIcon from '@material-ui/icons/Close';
import DeleteIcon from '@material-ui/icons/Delete';
import CreateCampaignButton from './CreateCampaignButton';
import SenderSelector from './SenderSelector';
import {reducer, initialState} from './emailStore';
import {stringify} from 'query-string'

const useStyles = makeStyles(theme => ({
    main_container: {
        position: 'absolute',
        top:'10%',
        left:'10%',
        width: '80%',
        maxHeight: '90vh',
        overflowY: 'scroll',
        backgroundColor: theme.palette.background.paper,
        border: '2px solid #000',
        boxShadow: theme.shadows[5],
        padding: theme.spacing(2, 4, 3),
    },
    editeur : {
        minHeight : '200px',
        marginBottom:'1rem',
    },
    ligne : {
        marginBottom:'1rem',
        position:'relative',
    },
    actionButton : {
        position: 'absolute',
        right : '0%',
        top : '5%',
    },
    bloc : {
        marginBottom:'1rem',
        borderStyle: 'dashed',
        borderWidth: 'thin',
        '&:hover' : {
            borderStyle: 'solid',

        }
    },
    closeIcon : {
        float: 'right',
        cursor: 'pointer',
        position: 'absolute',
        top: '10px',
        right: '5px',
        fontSize: '22px',

    }
}));

const MailComposer = ({isOpen, selectedIds, onClose, resource, basePath, filterValues,campagneInit, shouldFetch, limit=1000,campagneReprise}) => {

    const etats = {
        INITIALISE              : 0,
        CAMPAGNE_ENREGISTREE    : 1,
        MAIL_TEST_ENVOYE        : 2,
        MAIL_MASSE_ENVOYE       : 3
    }
    const classes = useStyles();
    const [expediteur, setExpediteur] = useState(null);
    const [corpsMail, dispatch] = useReducer(reducer, initialState);
    const [objetMail, setObjetMail] = useState("");
    const [elementSurvole, setElementSurvole] = useState(null);
    const [compteRendu, setCompteRendu] = useState("");
    const [etat, setEtat] = useState(etats.INITIALISE);
    const [loading, setLoading] = useState(false);
    const [ids, setIds] = useState(selectedIds);
    const [campagne, setCampagne] = useState(campagneInit);
    const dataProvider = useDataProvider();
    const apiUrlTest = process.env.REACT_APP_API+process.env.REACT_APP_CAMPAIGN_SEND_TEST;
    const apiUrlReel = process.env.REACT_APP_API+process.env.REACT_APP_CAMPAIGN_SEND;
    const dispatchTest = useDispatch();
    const [loadingTest, setLoadingTest] = useState(false);
    const token = localStorage.getItem('token');
    const unselectAll = useUnselectAll();

    // Impose de sauvegarder la campagne AVANT d'envoyer un mail
    const dispatchAndReset = values => {
        setEtat(etats.INITIALISE);
        dispatch(values)
    }

    // Sélection des destinataires à partir d'un filtre éventuel
    useEffect(() => {
        if (shouldFetch) {
            setLoading(true)
            dataProvider.getList(resource, {filter:filterValues, pagination:{ page: 1 , perPage: limit }, sort: { field: 'id', order: 'ASC' }, })
                .then(({ data }) => {
                    setIds(data.map(d => d.id));
                    setLoading(false);
                })
                .catch(error => {
                    setCompteRendu("Erreur lors de la sélection de tous les destinataires");
                    setLoading(false);
                })
        }
    }, [shouldFetch, filterValues, resource]);

    useEffect( ()=>{
        if (loading) {
            setCompteRendu("Chargement...")
        } else {
            setCompteRendu(`Votre mail va concerner ${ids.length} utilisateur${ids.length>1 ? "s" : ""}.`);
        }
    }, [ids, loading] )


    useEffect(() => {
        if (campagneReprise){
            setLoading(true);
            setCampagne(campagneReprise)
            setIds(selectedIds)
            setObjetMail(campagneReprise.subject)
            const obj = JSON.parse(campagneReprise.body);
            obj.map((obj) => {
                dispatchAndReset({type:'resume_campaign', obj})
            });
            setLoading(false);
        }
    }, [isOpen]);



    // Callback suite à la création / mise à jour d'une campagne
    const apresEnregistrementCampagne = nouvelleCampagne => {
        setEtat(etats.CAMPAGNE_ENREGISTREE)
        setCampagne(nouvelleCampagne)

    }
    // Sélection des éléments nécessaire à la création / mise à jour d'une campagne
    const campaignCreateParameters = expediteur ? {
            user    : expediteur.id,
            name    : objetMail,
            subject : objetMail,

            fromName : expediteur.fromName,
            email   : expediteur.replyTo,
            replyTo : expediteur.replyTo,

            body    : JSON.stringify(corpsMail),
            status  : 0,
            medium  : "/media/2",   // media#2 is email
            deliveries :  ids.map(id=>({user:id})),
        } : {}

    // Envoi du mail de test (si la campagne est sauvegardée)
    const handleClickEnvoiTest = () => {

        setLoading(true);
        const options = {}
        if (!options.headers) {
            options.headers = new Headers({ Accept: 'application/json' });
        }
        options.headers.set('Authorization', `Bearer ${token}`);

        let response =  fetchUtils.fetchJson(`${apiUrlTest}/${campagne.originId}`,options).then(({ json }) => ({
            data: json,

        }))
        setCompteRendu("Le mail de test a été envoyé à " + expediteur.replyTo);
        setEtat(etats.MAIL_TEST_ENVOYE);
        setLoading(false);

    };

    // Envoi du mail de masse (si la campagne est sauvegardée)
    const handleClickEnvoiMasse = () => {
        setLoading(true);
        const options = {}
        if (!options.headers) {
            options.headers = new Headers({ Accept: 'application/json' });
        }
        options.headers.set('Authorization', `Bearer ${token}`);

        let response =  fetchUtils.fetchJson(`${apiUrlReel}/${campagne.originId}`,options).then(({ json }) => ({
            data: json,

        }))
        setCompteRendu("Le mail a été envoyé aux " + (ids.length || 0) + " destinataires." );
        setEtat(etats.MAIL_MASSE_ENVOYE);
        setLoading(false);
        onClose();
       unselectAll(resource);
    };

    // Fonction utile à la modification d'un élément du mail
    const modifieLigneCorpsMail = (indice, nature) => e => {
        const valeur = e.target ? e.target.value : e
        dispatchAndReset({type:'update', indice, valeur, nature})
    }


    return (
        <Modal
            aria-labelledby="simple-modal-title"
            aria-describedby="simple-modal-description"
            open={isOpen}
        >
            <div className={classes.main_container}>
               <CloseIcon className={classes.closeIcon} onClick={() => onClose()}/>
                <Grid container direction="column" justify="flex-start" alignItems="stretch">
                    <Grid container direction="row" justify="space-between" alignItems="center">
                        <Grid item><h1>Nouvel envoi en masse</h1></Grid>
                        <Grid item>
                            <CreateCampaignButton campagne={campaignCreateParameters} oldCampaign={campagne} disabled={!objetMail} enregistrementSuccess={apresEnregistrementCampagne} >Enregistrer</CreateCampaignButton> &nbsp;
                            <Button variant="contained" color="secondary" onClick={() => onClose()}>Annuler</Button> &nbsp;

                            <Button variant="contained" disabled={etat<etats.CAMPAGNE_ENREGISTREE} onClick={handleClickEnvoiTest}>Envoyer Mail de test</Button> &nbsp;
                            <Button variant="contained" color="primary" disabled={etat<etats.MAIL_TEST_ENVOYE} onClick={handleClickEnvoiMasse}>Envoyer aux {ids.length || 0} destinataires</Button>&nbsp;

                        </Grid>
                    </Grid>
                    { compteRendu &&
                        <p> {loading ? <span><CircularProgress size={12} /> Envoi en cours ....</span> : compteRendu} </p>
                    }

                    <Grid container direction="row" justify="flex-start" alignItems="center" spacing={2} className={classes.ligne}>
                        <Grid item>Expéditeur : </Grid>
                        <Grid item>
                            <SenderSelector onExpediteurChange={nouvelExpediteur => setExpediteur(nouvelExpediteur) } />
                        </Grid>
                    </Grid>

                    <TextField fullWidth label="Objet du mail" variant="outlined" className={classes.ligne} value={objetMail} onChange={e=> setObjetMail(e.target.value)} />

                    <Grid container direction="row" justify="flex-start" alignItems="center" spacing={2} className={classes.ligne}>
                        <Grid item>Ajouter : </Grid>
                        <Grid item><Button variant="contained" onClick={()=>dispatchAndReset({type:'add_title'})} >Un titre</Button></Grid>
                        <Grid item><Button variant="contained" onClick={()=>dispatchAndReset({type:'add_text'})}>Un texte</Button></Grid>
                        <Grid item><Button variant="contained" onClick={()=>dispatchAndReset({type:'add_image'})}>Une image</Button></Grid>
                    </Grid>

                    { corpsMail.map( (d,i) => (
                        <Paper key={i} className={classes.ligne} onMouseEnter={()=>setElementSurvole(i)} onMouseLeave={()=>setElementSurvole(null)} >
                            { d.titre !== undefined && <TextField label="Titre" fullWidth variant="outlined" value={d.titre} onChange={modifieLigneCorpsMail(i, 'titre')} /> }
                            { d.image !== undefined && <ImageUpload imageSrc={d.image.src} imageId={d.image.id} setImage={modifieLigneCorpsMail(i, 'image')} campaignId={campagne.originId} />}
                            { d.texte !== undefined && <RichTextInput id={"email-compose"+i} value={d.texte} onChange={modifieLigneCorpsMail(i, 'texte')} /> }
                            { elementSurvole === i && <div className={classes.actionButton} >
                                <Fab color="primary" size="small" aria-label="Up" onClick={()=>dispatchAndReset({type:'up', indice:i})}><ArrowUpwardIcon /></Fab>
                                <Fab color="primary" size="small" aria-label="Down" onClick={()=>dispatchAndReset({type:'down', indice:i})}><ArrowDownwardIcon /></Fab>
                                <Fab color="secondary" size="small" aria-label="edit" onClick={()=>dispatchAndReset({type:'delete', indice:i})}><DeleteIcon /></Fab>
                            </div>}
                        </Paper>

                    ))}

                </Grid>
            </div>
        </Modal>

    )
}

export default MailComposer
