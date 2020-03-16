import React from 'react'
import { 
    Create, SimpleForm,
    TextInput,
    DateTimeInput, ReferenceInput, SelectInput, BooleanInput,
    FormDataConsumer, 
    ReferenceField, FunctionField,useTranslate
} from 'react-admin'
import RichTextInput from 'ra-input-rich-text';
import { makeStyles } from '@material-ui/core/styles'

import GeocompleteInput from '../Utilities/geocomplete'
import EventImageUpload from './EventImageUpload'
import EventDuration from './EventDuration'
import CurrentUserInput from '../Utilities/CurrentUserInput'

import { userOptionRenderer} from '../Utilities/renderers'

const useStyles = makeStyles({
    inlineBlock: { display: 'inline-flex', marginRight: '1rem' },
    fullwidth: { width:"100%", marginBottom:"1rem" },
    title : {fontSize:"1.5rem", fontWeight:"bold", width:"100%", marginBottom:"1rem"},
    richtext: { width:"100%", minHeight:"15rem", marginBottom:"1rem" },
});

export const EventCreate = (props) => {
    const classes = useStyles();
    const translate = useTranslate();

    const required = (message = 'ra.validation.required') =>
        value => value ? undefined : translate(message);

    return (
        <Create { ...props } title="Evénement > créer">
            <SimpleForm >
                <TextInput fullWidth source="name" label={translate('custom.label.event.name')} validate={[required()]} formClassName={classes.title} />
                <EventImageUpload formClassName={classes.fullwidth}/>
                <TextInput fullWidth source="description" label={translate('custom.label.event.resume')}  validate={required()} formClassName={classes.fullwidth}/>
                <RichTextInput variant="filled" source="fullDescription" label={translate('custom.label.event.resumefull')}  validate={required()} formClassName={classes.richtext} />
                
                <TextInput fullWidth source="url" type="url" label={translate('custom.label.event.site')}  formClassName={classes.fullwidth}/>
                <GeocompleteInput source="address" label={translate('custom.label.event.adresse')}  validate={required()} formClassName={classes.fullwidth}/>

                <BooleanInput label={translate('custom.label.event.setTime')}  source="useTime" initialValue={false} formClassName={classes.inlineBlock}/>
                <EventDuration formClassName={classes.inlineBlock}/>
                 <SelectInput label={translate('custom.label.event.status')} source="status" defaultValue={1} choices={[
                    { id: 0, name: 'Brouillon' },
                    { id: 1, name: 'Validé' },
                    { id: 2, name: 'Désactivé' },
                ]} formClassName={classes.inlineBlock}/>

                <CurrentUserInput source="user" label={translate('custom.label.event.createur')} />
                
                
                
                
            </SimpleForm>
        </Create>
    )
}
