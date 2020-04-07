import React, {useState,useCallback} from 'react';
import { useForm } from 'react-final-form';
import GeocompleteInput from "../Utilities/geocomplete";
import TerritoryInput from "../Utilities/territory";

import {
    Create,
    TabbedForm, FormTab,
    TextInput, SelectInput, DateInput,
    email, regex, ReferenceArrayInput, SelectArrayInput,BooleanInput,ReferenceInput,useTranslate, Toolbar,  SaveButton,
    useCreate,
    useRedirect,
    useNotify,
} from 'react-admin';
import { makeStyles } from '@material-ui/core/styles'

const useStyles = makeStyles({
    spacedHalfwidth: { width:"45%", marginBottom:"1rem", display:'inline-flex', marginRight: '1rem' },
    footer: { marginTop:"2rem" },
});

const UserCreate = props => {
    const classes = useStyles()
    const translate = useTranslate();
    const instance = process.env.REACT_APP_INSTANCE_NAME;

    const [fields, setFields] = useState([{ value: null }]);

    const required = (message = translate('custom.alert.fieldMandatory') ) =>
            value => value ? undefined : message;

    const minPassword = (message = 'Au minimum 8 caractères') =>
        value => value && value.length >= 8 ? undefined  : message;

    const upperPassword = regex(/^(?=.*[A-Z]).*$/ , translate('custom.label.user.errors.upperPassword')  );
    const lowerPassword = regex(/^(?=.*[a-z]).*$/ , translate('custom.label.user.errors.lowerPassword')  );
    const numberPassword = regex(/^(?=.*[0-9]).*$/ , translate('custom.label.user.errors.numberPassword')  );

    const genderChoices = [
        { id: 1, name: translate('custom.label.user.choices.women') },
        { id: 2, name: translate('custom.label.user.choices.men') },
        { id: 3, name: translate('custom.label.user.choices.other') },
    ];
    const smoke = [
        {id : 0, name : translate('custom.label.user.choices.didntSmoke')},
        {id : 1, name : translate('custom.label.user.choices.didntSmokeCar')},
        {id : 2, name : translate('custom.label.user.choices.smoke')},
    ];
    const musique = [
        {id : false, name :  translate('custom.label.user.choices.withoutMusic')},
        {id : true, name : translate('custom.label.user.choices.withMusic')},
    ];

    const bavardage = [
        {id : false, name : translate('custom.label.user.choices.dontTalk')},
        {id : true, name : translate('custom.label.user.choices.talk')},
    ];

    const phoneDisplay = [
        {id : 0, name : translate('custom.label.user.phoneDisplay.forAll')},
        {id : 1, name : translate('custom.label.user.phoneDisplay.forCarpooler')},
    ];

    function handleChange(i, event) {
      const values = [...fields];
      values[i].value = event.target.value;
      setFields(values);
    }

    function handleAdd() {
      console.info(fields)
      const values = [...fields];
      values.push({ value: null });
      setFields(values);
    }

    function handleRemove(i) {
      const values = [...fields];
      values.splice(i, 1);
      setFields(values);
    }

    const SaveWithNoteButton = ({ handleSubmitWithRedirect, ...props }) => {
        const [create] = useCreate('posts');
        const redirectTo = useRedirect();
        const notify = useNotify();
        const { basePath, redirect } = props;
        const form = useForm();
        const handleClick = useCallback(() => {

          console.info(fields)
            // change the average_note field value
            form.change('average_note', 10);
            handleSubmitWithRedirect('edit');
        }, [form]);
        // override handleSubmitWithRedirect with custom logic
        return <SaveButton {...props} handleSubmitWithRedirect={handleClick} />;
    };


    const PostCreateToolbar = props => (
          <Toolbar {...props}>
            <SaveWithNoteButton
                label="post.action.save_and_show"
                redirect="show"
                submitOnEnter={true}
            />
          </Toolbar>
      );

    const validateRequired = [required()];
    const paswwordRules = [required(),minPassword(),upperPassword,lowerPassword,numberPassword];
    const emailRules = [required(), email() ];
    const validateUserCreation = values => values.address ? {} :  ({ address : "L'adresse est obligatoire" })

    return (
        <Create { ...props } title={translate('custom.label.user.title.create')}>
            <TabbedForm validate={validateUserCreation} initialValues={{newsSubscription:true}} toolbar={<PostCreateToolbar />} >
                <FormTab label={translate('custom.label.user.indentity')}>
                    <TextInput fullWidth required source="email" label={translate('custom.label.user.email')} validate={ emailRules } formClassName={classes.spacedHalfwidth} />
                    <TextInput fullWidth required source="password" label={translate('custom.label.user.password')} type="password" validate={ paswwordRules } formClassName={classes.spacedHalfwidth}/>

                    <TextInput fullWidth required source="familyName" label={translate('custom.label.user.familyName')} validate={ validateRequired } formClassName={classes.spacedHalfwidth} />
                    <TextInput fullWidth required source="givenName" label={translate('custom.label.user.givenName')} validate={ validateRequired } formClassName={classes.spacedHalfwidth}/>
                    <SelectInput required source="gender" label={translate('custom.label.user.gender')} choices={genderChoices} validate={ validateRequired } formClassName={classes.spacedHalfwidth}/>
                    <DateInput required source="birthDate" label={translate('custom.label.user.birthDate')} validate={ validateRequired } formClassName={classes.spacedHalfwidth}/>

                    <TextInput required source="telephone" label={translate('custom.label.user.telephone')} validate={ validateRequired } formClassName={classes.spacedHalfwidth}/>

                    <BooleanInput fullWidth label={translate('custom.label.user.newsSubscription',{ instanceName: instance })} source="newsSubscription" formClassName={classes.spacedHalfwidth} />

                    <SelectInput fullWidth source="phoneDisplay" label={translate('custom.label.user.phoneDisplay.visibility')} choices={phoneDisplay}  formClassName={classes.spacedHalfwidth}/>

                    <GeocompleteInput fullWidth source="addresses" label={translate('custom.label.user.adresse')} validate={required("L'adresse est obligatoire")}/>

                    <BooleanInput initialValue={true} label={translate('custom.label.user.accepteReceiveEmail')} source="newsSubscription" />

                </FormTab>
                <FormTab label={translate('custom.label.user.preference')}>
                <SelectInput fullWidth source="music" label={translate('custom.label.user.carpoolSetting.music')} choices={musique} formClassName={classes.spacedHalfwidth}/>
                <TextInput fullWidth source="musicFavorites" label={translate('custom.label.user.carpoolSetting.musicFavorites')} formClassName={classes.spacedHalfwidth}/>
                <SelectInput fullWidth source="chat" label={translate('custom.label.user.carpoolSetting.chat')} choices={bavardage} formClassName={classes.spacedHalfwidth}/>
                <TextInput fullWidth source="chatFavorites" label={translate('custom.label.user.carpoolSetting.chatFavorites')} formClassName={classes.spacedHalfwidth}/>
                <SelectInput fullWidth source="smoke" label={translate('custom.label.user.carpoolSetting.smoke')} choices={smoke} formClassName={classes.spacedHalfwidth}/>
                </FormTab>

                  <FormTab label={translate('custom.label.user.manageRoles')}>

                        <button type="button" onClick={() => handleAdd()}>
                          +
                        </button>

                        {fields.map((field, idx) => {
                          return (
                            <div key={`div-${field}-${idx}`} fullwidth="true">

                              <ReferenceArrayInput key={`role-${field}-${idx}`} required label={translate('custom.label.user.roles')}
                                source="userAuthAssignments" reference="permissions/roles" validate={ validateRequired }
                                formClassName={classes.spacedHalfwidth}>
                                  <SelectArrayInput optionText="name" />
                              </ReferenceArrayInput>

                              <TerritoryInput key={`territory-${field}-${idx}`} source="userTerritories"
                                label={translate('custom.label.user.territory')} value={field.value}  formClassName={classes.spacedHalfwidth}
                                validate={required("L'adresse est obligatoire")}/>

                              <button type="button" onClick={() => handleRemove(idx)}>
                                X
                              </button>
                            </div>
                          );
                        })}

                  </FormTab>


            </TabbedForm>
        </Create>
    );

};
export default UserCreate
