import React from 'react';
import isAuthorized from '../Utilities/authorization';

//import bcrypt from 'bcryptjs';

import { 
    Show,
    TabbedShowLayout, Tab,
    TextField, EmailField, DateField, 
    EditButton
} from 'react-admin';

export const UserShow = (props) => (
    <Show { ...props } title="Utilisateurs > afficher">
        <TabbedShowLayout>
            <Tab label="Identité">
                <TextField source="originId" label="ID"/>
                <TextField source="givenName" label="Prénom"/>
                <TextField source="familyName" label="Nom"/>
                <EmailField source="email" label="Email" />
                <DateField source="createdDate" label="Date de création"/>
                <EditButton />
            </Tab>
            <Tab label="Préférences">

            </Tab>
            <Tab label="Adresses">

            </Tab>
            {isAuthorized("permission_manage") && 
            <Tab label="Droits">
                
            </Tab>}
        </TabbedShowLayout>
    </Show>
);