import React from 'react';
import { 
    Create,
    SimpleForm, 
    required,
    ReferenceInput, SelectInput, AutocompleteInput
} from 'react-admin';
import { makeStyles } from '@material-ui/core/styles'
import { parse } from "query-string";
import { statusChoices } from '../Community/communityChoices'
import FullNameField from '../../User/FullNameField'

const useStyles = makeStyles({
    halfwidth: { width:"50%", marginBottom:"1rem" },
    title : {fontSize:"1.5rem", fontWeight:"bold", width:"100%", marginBottom:"1rem"}
})



export const CommunityUserCreate = (props) => {
    const classes = useStyles()
    const { community: community_string } = parse(props.location.search);
    const community = `/communities/${community_string}`

    const community_uri = encodeURIComponent(community);
    const redirect = community_uri ? `/communities/${community_uri}` : 'show';

    const inputText = choice => {
        console.log("Choice inputText", choice)
        return `${choice.givenName} ${choice.familyName || choice.shortFamilyName}`
    }

    return (
    <Create { ...props } title="Communautés > ajouter un membre">
        <SimpleForm
            defaultValue={{ community }}
            redirect={redirect}
        >
            <ReferenceInput fullWidth label="Communauté" source="community" reference="communities" validate={required()} formClassName={classes.title}>
                <SelectInput optionText="name"/>
            </ReferenceInput>

            <ReferenceInput label="Nouveau Membre" source="user" reference="users" validate={required()} formClassName={classes.halfwidth}>
                {/* Should be like that : 
                    <AutocompleteInput inputText={inputText} optionValue="id" optionText={<FullNameField />} matchSuggestion={(filterValue, suggestion) => true} allowEmpty={false}/>
                    But https://github.com/marmelab/react-admin/pull/4367
                    So waiting for the next release of react-admin 
                */}
                <AutocompleteInput optionValue="id" optionText={inputText} allowEmpty={false}/>

            </ReferenceInput>

            <SelectInput label="Statut" source="status" choices={statusChoices} defaultValue={1} validate={required()} formClassName={classes.halfwidth}/>
        </SimpleForm>
    </Create>
    );
}