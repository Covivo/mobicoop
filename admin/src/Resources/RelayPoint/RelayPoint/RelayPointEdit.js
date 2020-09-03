import React from 'react';
import RichTextInput from 'ra-input-rich-text';

import {
  Edit,
  TabbedForm,
  FormTab,
  required,
  TextInput,
  BooleanInput,
  ReferenceInput,
  SelectInput,
  NumberInput,
  FunctionField,
  ReferenceField,
  useTranslate,
  FormDataConsumer,
} from 'react-admin';

import GeocompleteInput from '../../../components/geolocation/geocomplete';
import { addressRenderer, UserRenderer } from '../../../utils/renderers';
import RelayPointImageUpload from './RelayPointImageUpload';

const userId = `/users/${localStorage.getItem('id')}`;
const statusChoices = [
  { id: 0, name: 'En attente' },
  { id: 1, name: 'Actif' },
  { id: 2, name: 'Inactif' },
];

const isLatitude = (lat) => isFinite(lat) && Math.abs(lat) <= 90;
const isLongitude = (lng) => isFinite(lng) && Math.abs(lng) <= 180;

export const RelayPointEdit = (props) => {
  const translate = useTranslate();

  const validateLatitude = (value) =>
    !isLatitude(value) ? translate('custom.label.relayPoint.invalidLatitude') : undefined;

  const validateLongitude = (value) =>
    !isLongitude(value) ? translate('custom.label.relayPoint.invalidLongitude') : undefined;

  return (
    <Edit {...props} title="Points relais > éditer">
      <TabbedForm>
        <FormTab label="Identité">
          <ReferenceInput source="user" label="Créateur" reference="users" defaultValue={userId}>
            <SelectInput optionText={<UserRenderer />} />
          </ReferenceInput>
          <TextInput source="name" label="Nom" validate={required()} />
          <ReferenceField
            source="address.id"
            label="Adresse actuelle"
            reference="addresses"
            link={false}
          >
            <FunctionField render={addressRenderer} />
          </ReferenceField>
          <GeocompleteInput source="address" label="Nouvelle addresse" />
          <SelectInput source="status" label="Status" choices={statusChoices} defaultValue={1} />
          <ReferenceInput source="relayPointType.id" label="Types" reference="relay_point_types">
            <SelectInput optionText="name" />
          </ReferenceInput>
          <TextInput source="description" label="Description" validate={required()} />
          <RichTextInput
            source="fullDescription"
            label="Description complète"
            validate={required()}
          />
          <NumberInput source="address.latitude" validate={[validateLatitude]} />
          <NumberInput source="address.longitude" validate={[validateLongitude]} />
        </FormTab>
        <FormTab label="Communauté">
          <ReferenceInput source="community" label="Communauté" reference="communities" resettable>
            <SelectInput optionText="name" />
          </ReferenceInput>
          <FormDataConsumer>
            {({ formData, ...rest }) =>
              formData.community && (
                <BooleanInput source="private" label="Privé à cette communauté" {...rest} />
              )
            }
          </FormDataConsumer>
        </FormTab>
        <FormTab label="Propriétés">
          <NumberInput source="places" label="Nombre de places" />
          <NumberInput source="placesDisabled" label="Nombre de places handicapés" />
          <BooleanInput source="free" label="Gratuit" defaultValue={true} />
          <BooleanInput source="secured" label="Sécurisé" />
          <BooleanInput source="official" label="Officiel" />
          <BooleanInput source="suggested" label="Suggestion autocomplétion" />
        </FormTab>
        <FormTab label="Images">
          <RelayPointImageUpload label="Images" />
        </FormTab>
      </TabbedForm>
    </Edit>
  );
};
