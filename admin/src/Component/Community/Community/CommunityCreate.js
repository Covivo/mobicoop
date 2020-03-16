import React from 'react';
import { 
    Create,
    SimpleForm, required,
    TextInput, BooleanInput, ReferenceInput, SelectInput, DateInput, 
    ReferenceField, FunctionField,useTranslate
} from 'react-admin';
import RichTextInput from 'ra-input-rich-text';
import { makeStyles } from '@material-ui/core/styles'
import GeocompleteInput from '../../Utilities/geocomplete';
import { UserRenderer, } from '../../Utilities/renderers'
import {validationChoices, statusChoices } from './communityChoices'

const userId = `/users/${localStorage.getItem('id')}`;

const useStyles = makeStyles({
    hiddenField: { display:"none" },
    fullwidth: { width:"100%", marginBottom:"1rem" },
    fullwidthDense: { width:"100%" },
    richtext: { width:"100%", minHeight:"15rem", marginBottom:"1rem" },
    title : {fontSize:"1.5rem", fontWeight:"bold", width:"100%", marginBottom:"1rem"}
})

export const CommunityCreate = (props) => {
    const classes = useStyles();
    const translate = useTranslate();
    const user  = `/users/${localStorage.getItem('id')}`
    return (
        <Create { ...props } title="Communautés > ajouter">
            <SimpleForm initialValues={{ user }} redirect="list">
                <TextInput fullWidth source="name" label={translate('custom.label.community.name')} validate={required()} formClassName={classes.title}/>
                <GeocompleteInput source="address" label={translate('custom.label.community.adress')} validate={required()} formClassName={classes.fullwidth}/>

                <BooleanInput source="membersHidden" label={translate('custom.label.community.memberHidden')}  />
                <BooleanInput source="proposalsHidden" label={translate('custom.label.community.proposalHidden')} />
                <SelectInput source="validationType" label={translate('custom.label.community.validationType')} choices={validationChoices} />
                <TextInput fullWidth source="domain" label={translate('custom.label.community.domainName')} />

                <TextInput fullWidth source="description" label={translate('custom.label.community.description')} validate={required()} formClassName={classes.fullwidth}/>
                <RichTextInput fullWidth variant="filled" source="fullDescription" label={translate('custom.label.community.descriptionFull')} validate={required()} formClassName={classes.richtext} />

                <DateInput disabled source="createdDate" label={translate('custom.label.community.createdDate')} />
                <DateInput disabled source="updatedDate" label={translate('custom.label.community.updateDate')} />
                <TextInput disabled source="status" label={translate('custom.label.community.status')} />
                <ReferenceInput fullWidth source="user" label={translate('custom.label.community.createdBy')}   reference="users" >
                    <SelectInput optionText={<UserRenderer />} />
                </ReferenceInput>

            </SimpleForm>
        </Create>
    )
}