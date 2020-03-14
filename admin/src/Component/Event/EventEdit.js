import React from 'react'

import { 
    Edit, SimpleForm,
    TextInput,
    BooleanInput, ReferenceInput, SelectInput,
    FormDataConsumer, 
    ReferenceField, FunctionField,useTranslate
} from 'react-admin'
import RichTextInput from 'ra-input-rich-text';
import { makeStyles } from '@material-ui/core/styles'

import GeocompleteInput from '../Utilities/geocomplete'
import EventImageUpload from './EventImageUpload'
import EventDuration from './EventDuration'
import {addressRenderer, UserRenderer} from '../Utilities/renderers'

const useStyles = makeStyles({
    fullwidth: { width:"100%" },
    spacedFullwidth: { width:"100%", marginBottom:"1rem" },
    title : {fontSize:"1.5rem", fontWeight:"bold", width:"100%", marginBottom:"1rem"},
    richtext: { width:"100%", minHeight:"15rem", marginBottom:"1rem" },
    inlineBlock: { display: 'inline-flex', marginRight: '1rem' },
    footer: { marginTop:"2rem" },
});



export const EventEdit = (props) => {

    const translate = useTranslate();
    const classes = useStyles()

    const required = (message = 'ra.validation.required') =>
        value => value ? undefined : translate(message);
    
    return (
        <Edit { ...props } title="Evénement > éditer">
            <SimpleForm >
                <TextInput fullWidth source="name" label="" validate={[required()]} formClassName={classes.title} />
                <EventImageUpload formClassName={classes.fullwidth}/>
                <TextInput fullWidth source="description" label="Description" validate={required()} formClassName={classes.fullwidth}/>
                <RichTextInput variant="filled" source="fullDescription" label="Description complète" validate={required()} formClassName={classes.richtext} />
                <TextInput fullWidth source="url" type="url" label="Site Internet" formClassName={classes.spacedFullwidth}/>
                <ReferenceField source="address" label="Adresse actuelle" reference="addresses" link="" className={classes.fullwidth}>
                    <FunctionField render={addressRenderer} />
                </ReferenceField>

                <GeocompleteInput source="address" label="Nouvelle adresse" validate={required()} formClassName={classes.spacedFullwidth}/>

                <BooleanInput label="Préciser l'heure" source="useTime" initialValue={false} formClassName={classes.inlineBlock}/>
                <EventDuration formClassName={classes.inlineBlock}/>

                <ReferenceInput source="user" label="Créateur" reference="users" formClassName={classes.footer}>
                    <SelectInput optionText={<UserRenderer />} />
                </ReferenceInput>
                
                <SelectInput source="status" choices={[
                    { id: 0, name: 'Brouillon' },
                    { id: 1, name: 'Validé' },
                    { id: 2, name: 'Désactivé' },
                ]} formClassName={classes.inlineBlock}/>
                
            </SimpleForm>
        </Edit>
    )
}
