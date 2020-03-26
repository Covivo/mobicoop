import React from 'react';
import { 
    Edit,
    SimpleForm, 
    ReferenceInput, SelectInput,
    required
} from 'react-admin';
import { makeStyles } from '@material-ui/core/styles'
import { UserRenderer } from '../../Utilities/renderers'
import { statusChoices } from '../Community/communityChoices'

const useStyles = makeStyles({
    title : {fontSize:"1.5rem", fontWeight:"bold", width:"100%", marginBottom:"1rem"}
})

export const CommunityUserEdit = (props) => {
    const classes   =  useStyles()
    const redirect  =  props.location.backTo || '/communities/'
    console.log("CommunityUserEdit props :", props)
    return (
    <Edit { ...props } title="Communautés > éditer un membre">
        <SimpleForm
            redirect={redirect}
        >
            <ReferenceInput fullWidth label="Communauté" source="community" reference="communities" validate={required()} formClassName={classes.title}>
                <SelectInput optionText="name"/>
            </ReferenceInput>

            <ReferenceInput label="Nouveau Membre" source="user" reference="users" validate={required()} >
                <SelectInput optionText={<UserRenderer />}/>
            </ReferenceInput>

            <SelectInput label="Statut" source="status" choices={statusChoices} defaultValue={1} validate={required()} />

        </SimpleForm>
    </Edit>
    );
}