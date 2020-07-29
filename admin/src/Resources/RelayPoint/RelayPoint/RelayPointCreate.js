import React, { useState } from 'react';
import RichTextInput from 'ra-input-rich-text';
import Snackbar from '@material-ui/core/Snackbar';
import IconButton from '@material-ui/core/IconButton';
import CloseIcon from '@material-ui/icons/Close';

import {
  Create,
  TabbedForm,
  FormTab,
  required,
  TextInput,
  BooleanInput,
  ReferenceInput,
  SelectInput,
  NumberInput,
  FormDataConsumer,
  useTranslate,
  SaveButton,
  Toolbar,
} from 'react-admin';

import GeocompleteInput from '../../../components/geolocation/geocomplete';


const RelayPointCreate = (props) => {

  const [showSnack, setShowSnack] = useState(false);
  const [errors, setErrors] = useState();
  const translate = useTranslate();

  const userOptionRenderer = (choice) => `${choice.givenName} ${choice.familyName}`;
  const userId = `/users/${localStorage.getItem('id')}`;
  const statusChoices = [
    { id: 0, name: 'En attente' },
    { id: 1, name: 'Actif' },
    { id: 2, name: 'Inactif' },
  ];


  //Used for check if adresses is not empty 
  const validateRelayPointCreation = (values) => {
    let errors = {};
    if (!values.address) {
      setErrors(translate('custom.label.user.adresseMandatory'));
      errors = { error: 'error' };
    } else {
      setErrors();
    }
    return errors;
  };
  const displayError = () => {
    if (errors) setShowSnack(true);
  };
  const handleClose = () => {
    setShowSnack(false);
  };
  const PostCreateToolbar = (props) => (
    <Toolbar {...props}>
      <SaveButton onClick={displayError} />
    </Toolbar>
  );
  return (
    <Create {...props} title="Points relais > ajouter">
      <TabbedForm validate={validateRelayPointCreation} toolbar={<PostCreateToolbar />}>
        <FormTab label="Identité">
          <Snackbar
            anchorOrigin={{
              vertical: 'top',
              horizontal: 'center',
            }}
            open={showSnack}
            onClose={handleClose}
            message={errors}
            autoHideDuration={2000}
            action={
              <div>
                <IconButton aria-label="close" color="inherit" onClick={handleClose}>
                  <CloseIcon />
                </IconButton>
              </div>
            }
          />
          <ReferenceInput source="user" label="Créateur" reference="users" defaultValue={userId}>
            <SelectInput optionText={userOptionRenderer} />
          </ReferenceInput>
          <TextInput source="name" label="Nom" validate={required()} />
          <GeocompleteInput source="address" label="Adresse" validate={required()} />
          <SelectInput source="status" label="Status" choices={statusChoices} defaultValue={1} />
          <ReferenceInput source="relayPointType" label="Types" reference="relay_point_types">
            <SelectInput optionText="name" />
          </ReferenceInput>
          <TextInput source="description" label="Description" validate={required()} />
          <RichTextInput
            source="fullDescription"
            label="Description complète"
            validate={required()}
          />
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
        {/* <FormTab label="Images">
                <ImageInput source="images" label="Images" accept="image/*">
                    <ImageField source="src" title="title" />
                </ImageInput>
            </FormTab> */}
      </TabbedForm>
    </Create>
  )
};

export default RelayPointCreate;