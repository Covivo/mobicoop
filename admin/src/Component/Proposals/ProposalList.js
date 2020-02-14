import React from 'react';
import {Card, Grid, Avatar, LinearProgress, Button, Chip, Stepper, Step, StepLabel, Divider, List, ListItem, ListItemAvatar, ListItemText, ListItemSecondaryAction} from '@material-ui/core';
import { makeStyles } from '@material-ui/core/styles';
import RoomIcon from '@material-ui/icons/Room';
import fakeProposal from './fakeProposal';
import DropDownButton from '../Utilities/DropDownButton';

const useStyles = makeStyles(theme => ({
    main_panel: {
        backgroundColor: 'white',
        padding: theme.spacing(2, 4, 3),
        marginTop : "2rem",
    },
    card : {
        padding: theme.spacing(2, 4, 3),
        marginBottom : "2rem",
    },
    progress : {
        width:'200px',
    },
    path : {
        width:"50%",
    },
    quarter : {
        width: "25%",
    },
    divider : {
        marginBottom : "1rem",
    },
}));


const ProposalList = props => {
    const classes = useStyles();

    // For test only. Should be sourced by props
    const record = fakeProposal
    console.log(record)

    const { monCheck, tueCheck, wedCheck, thuCheck, friCheck, satCheck, sunCheck} = record.criteria;
    const { createdDate, updatedDate, id} = record;
    const { givenName, familyName, phone, avatars} = record.user;

    const DayChip = ({condition, label}) => condition ? <Chip label={label} color="primary" /> : <Chip label={label} />

    return (
        <Card className={classes.main_panel}>
            <Card raised className={classes.card}>
                <Grid container direction="row" justify="space-between" alignItems="center" spacing={2}>
                    <Grid item>
                        <Grid container direction="row" justify="flex-start" alignItems="center" spacing={2}>
                            <Grid item><Avatar alt="Remy Sharp" src={avatars.length && avatars[0]} /></Grid>
                            <Grid item><h2>{givenName} {familyName}</h2></Grid>
                            <Grid item><small>{phone}</small></Grid>
                            <Grid item><DropDownButton label="Contacter demandeur" options={["SMS", "Email", "Téléphone"]} /></Grid>
                        </Grid>
                    </Grid>
                    <Grid item><Button variant="contained" color="primary">Editer</Button></Grid>
                </Grid>

                <Grid container direction="row" justify="space-between" alignItems="center" spacing={2}>
                    <Grid item><h1>Demande solidaire #{id}</h1></Grid>
                    <Grid item>
                        <Grid container direction="column" justify="space-between" alignItems="stretch" spacing={1}>
                            <Grid item className={classes.progress}><LinearProgress variant="determinate" value={63} /></Grid>
                            <Grid item>Recherche de solution</Grid>
                        </Grid>
                    </Grid>
                    <Grid item>
                        <Grid container direction="column" justify="space-between" alignItems="stretch" spacing={1}>
                            <Grid item><b>Dernière modification</b></Grid>
                            <Grid item>{updatedDate}</Grid>
                        </Grid>
                    </Grid>
                    <Grid item>
                        <Grid container direction="column" justify="space-between" alignItems="stretch" spacing={1}>
                            <Grid item><b>Création</b></Grid>
                            <Grid item>{createdDate}</Grid>
                        </Grid>
                    </Grid>
                </Grid>
                <Grid container direction="row" justify="flex-start" alignItems="center" spacing={2}>
                    <Grid item><b>Objet du déplacement :</b></Grid>
                    <Grid item>Emploi intérimaire</Grid>
                </Grid>

                <Grid container direction="row" justify="space-between" alignItems="center" spacing={2}>
                    <Grid item>Aller &lt;-&gt; Retour  <i>Les horaires diffèrent selon les jours</i></Grid>
                    <Grid item>
                        { [
                            { label: "L", condition:monCheck},
                            { label: "M", condition:tueCheck},
                            { label: "Me", condition:wedCheck},
                            { label: "J", condition:thuCheck},
                            { label: "V", condition:friCheck},
                            { label: "S", condition:satCheck},
                            { label: "D", condition:sunCheck},
                            ].map( ({label, condition})=> <DayChip key={label} label={label} condition={condition} />)
                        }
                    </Grid>
                </Grid>

                <Divider light/>
                <Grid container direction="row" justify="center" alignItems="center" spacing={2}>
                    <Grid item className={classes.path}>
                        <Stepper>
                            <Step active key={1}><StepLabel icon={<RoomIcon />} ><b>Morlaix</b></StepLabel></Step>
                            <Step active key={1}><StepLabel icon={<RoomIcon />}>1 Place de la mairie<br/><b>Carhaix-Plouguer</b></StepLabel></Step>
                        </Stepper>
                    </Grid>
                </Grid>

                <Divider light className={classes.divider}/>

                <Grid container direction="row" justify="flex-start" alignItems="center" spacing={2}>
                    <Grid item className={classes.quarter}><b>Autres besoins :</b></Grid>
                    <Grid item className={classes.quarter}>Venir me chercher à la porte</Grid>
                </Grid>

                <Grid container direction="row" justify="flex-start" alignItems="center" spacing={2}>
                    <Grid item className={classes.quarter}><b>Structure accompagnante :</b></Grid>
                    <Grid item className={classes.quarter}>Autre "Association BAJ"</Grid>
                    <Grid item className={classes.quarter}><b>Opérateur ayant enregistré la demande :</b></Grid>
                    <Grid item className={classes.quarter}>Solenne Ayzel</Grid>
                </Grid>
            </Card>

            <Card raised className={classes.card}>
                <Grid container direction="row" justify="space-between" alignItems="center" spacing={2}>
                    <Grid item><b>Conducteurs potentiels</b></Grid>
                    <Grid item><Button variant="contained" color="primary">Rechercher nouveau conducteur</Button></Grid>
                </Grid>

                <List>
                    <ListItem>
                        <ListItemAvatar><Avatar alt="Remy Sharp" src="/static/images/avatar/1.jpg"></Avatar></ListItemAvatar>
                        <ListItemText primary="Umberto Picaldi" secondary="+33 12346536543" />
                        <ListItemSecondaryAction><DropDownButton size="small" label="Contacter conducteur" options={["SMS", "Email", "Téléphone"]} /></ListItemSecondaryAction>
                    </ListItem>
                    <ListItem>
                        <ListItemAvatar><Avatar alt="Remy Sharp" src="/static/images/avatar/1.jpg"></Avatar></ListItemAvatar>
                        <ListItemText primary="Marcel Proust" secondary="+33 12346536543" />
                        <ListItemSecondaryAction><DropDownButton size="small" label="Contacter conducteur" options={["SMS", "Email", "Téléphone"]} /></ListItemSecondaryAction>
                    </ListItem>
                </List>
            </Card>

            <Card raised className={classes.card}>
                <Grid container direction="row" justify="space-between" alignItems="center" spacing={2}>
                    <Grid item><b>Dernière action</b></Grid>
                    <Grid item><Button variant="contained" color="primary">Nouvelle action</Button></Grid>
                </Grid>

                <List>
                    <ListItem>
                        <ListItemAvatar><Avatar alt="Remy Sharp" src="/static/images/avatar/1.jpg"></Avatar></ListItemAvatar>
                        <ListItemText primary="Solenne Ayzel" secondary="20/02/2020 14:35" />
                        <ListItemText primary="Contact d'un conducteur par mail" secondary="Covoitureur : Umberto Picaldi"/>
                    </ListItem>
                    <ListItem>
                        <a href='#'>Voir toutes les actions</a>
                    </ListItem>
                </List>
            </Card>

        </Card>
    )
}

/*
const ProposalList = props => (
    <ListGuesser {...props} filter={{ "criteria.solidary": true }}>
        <FieldGuesser source={"type"} />
        <FieldGuesser source={"comment"} />
        <FieldGuesser source={"private"} />
        <FieldGuesser source={"paused"} />
        <FieldGuesser source={"createdDate"} />
        <FieldGuesser source={"updatedDate"} />
        <FieldGuesser source={"proposalLinked"} />
        <FieldGuesser source={"user"} />
        <FieldGuesser source={"userDelegate"} />
        <FieldGuesser source={"waypoints"} />
        <FieldGuesser source={"travelModes"} />
        <FieldGuesser source={"communities"} />
        <FieldGuesser source={"matchingOffers"} />
        <FieldGuesser source={"matchingRequests"} />
        <FieldGuesser source={"criteria"} />
        <FieldGuesser source={"individualStops"} />
        <FieldGuesser source={"notifieds"} />
        <FieldGuesser source={"matchingProposal"} />
        <FieldGuesser source={"matchingLinked"} />
        <FieldGuesser source={"askLinked"} />
        <FieldGuesser source={"formalAsk"} />
        <FieldGuesser source={"results"} />
        <FieldGuesser source={"event"} />
    </ListGuesser>
);
*/

export default ProposalList;
