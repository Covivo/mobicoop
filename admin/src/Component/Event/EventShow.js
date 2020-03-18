import React from 'react';


import { 
    Show,
    SimpleShowLayout, Labeled,
    TabbedShowLayout, Tab,
    RichTextField, TextField, ReferenceField, SelectField,
    ImageField, DateField, FunctionField, UrlField
} from 'react-admin';

import {addressRenderer, UserRenderer} from '../Utilities/renderers'

import { makeStyles } from '@material-ui/core/styles';

const useStyles = makeStyles({
    form: { display: 'flex', flexWrap:'wrap' },
    imagewidth: { width:"150px" },
    quarterwidth: { width:"25%" },
    fullwidth: { width:"100%" },
    title : {fontSize:"1.5rem", fontWeight:"bold", width:"100%", marginBottom:"1rem" }
});


export const EventShow = (props) => {
    const classes = useStyles();
    return (
    <Show { ...props } title="Evénement > afficher" >
        <SimpleShowLayout className={classes.form}>
                <ReferenceField reference="images" source="images[0]" addLabel={false} className={classes.fullwidth}>
                    <ImageField source="versions.square_100"/>
                </ReferenceField>
                <TextField source="name" className={classes.title} addLabel={false}/>
                <TextField source="description" addLabel={false} className={classes.fullwidth}/>
                <RichTextField source="fullDescription" addLabel={false}/>
                <UrlField source="url" className={classes.fullwidth} label="Site internet"/>

                <ReferenceField reference="addresses" source="address" label="Adresse" className={classes.fullwidth}>
                    <FunctionField render={addressRenderer} />
                </ReferenceField>

                <DateField source="fromDate" label="Date de début" showTime className={classes.quarterwidth}/>
                <DateField source="toDate" label="Date de fin" showTime className={classes.quarterwidth}/>

                <ReferenceField reference="users" source="user" addLabel={false} className={classes.quarterwidth}>
                    <Labeled label="Créé par">
                        <FunctionField render={record => <UserRenderer record={record} />} />
                    </Labeled>
                </ReferenceField>
                
                <SelectField source="status" label="Etat" className={classes.quarterwidth} choices={[
                    { id: 0, name: 'Brouillon' },
                    { id: 1, name: 'Validé' },
                    { id: 2, name: 'Désactivé' },
                ]} />
            

        </SimpleShowLayout>
    </Show>
    )
}


