import React, { useEffect, useState, useMemo } from 'react';
import { useDataProvider } from 'react-admin';
import { RadioGroup, FormControlLabel, Radio } from '@material-ui/core';

const defaultSender = {
  replyTo: process.env.REACT_APP_SENDER_EMAIL_DEFAULT,
  fromName: process.env.REACT_APP_SENDER_FROM_NAME_DEFAULT,
  originId: 0,
}; // Should be in config file

const SenderSelector = ({ onExpediteurChange }) => {
  const [expeditors, setExpeditors] = useState([]);
  const [choix, setChoix] = useState(0);
  const dataProvider = useDataProvider();

  useEffect(() => {
    const userId = localStorage.getItem('id');

    userId &&
      dataProvider
        .getOne('users', { id: `/users/${userId}` })
        .then(({ data }) => {
          setExpeditors([
            { ...defaultSender, id: data.id },
            {
              replyTo: data.email,
              fromName: `${data.givenName} ${data.familyName}`,
              id: data.id,
              originId: data.originId,
            },
          ]);

          onExpediteurChange({ ...defaultSender, id: data.id });
        })
        .catch((error) => {
          setExpeditors([defaultSender]);
          onExpediteurChange({ ...defaultSender, id: `/users/${userId}` });
          console.log("Erreur lors de la recherche de l'utilisateur courant :", error);
        });
    return;
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, []);

  const changeChoix = (e) => {
    const nouveauChoix = parseInt(e.target.value, 10);
    setChoix(nouveauChoix);
    onExpediteurChange(expeditors[nouveauChoix]);
  };

  if (expeditors.length === 0) return <p>Chargement des expéditeurs...</p>;

  return (
    <RadioGroup row aria-label="expediteur" name="expediteur" value={choix} onChange={changeChoix}>
      {expeditors.map((expediteur, indice) => (
        <FormControlLabel
          key={expediteur.originId}
          value={indice}
          control={<Radio />}
          label={`${expediteur.fromName} < ${expediteur.replyTo} >`}
        />
      ))}
    </RadioGroup>
  );
};

export default SenderSelector;
