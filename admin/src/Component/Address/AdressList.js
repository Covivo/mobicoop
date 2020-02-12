import React from 'react';
import { ListGuesser, FieldGuesser} from '@api-platform/admin/lib';

const AddressList = props => (
    <ListGuesser {...props}>
        <FieldGuesser source={"name"} label="Nom"/>
        <FieldGuesser source={"addressLocality"} label="Ville"/>
        <FieldGuesser source={"postalCode"} label="Code postal"/>

        <FieldGuesser source={"houseNumber"} label="Numéro"/>
        <FieldGuesser source={"street"} label="Rue"/>
        <FieldGuesser source={"streetAddress"} label="Rue"/>
                
        <FieldGuesser source={"venue"} />
        <FieldGuesser source={"home"} />
        <FieldGuesser source={"displayLabel"} />
        <FieldGuesser source={"relayPoint"} />
        <FieldGuesser source={"event"} />
        
    </ListGuesser>
);

export default AddressList

