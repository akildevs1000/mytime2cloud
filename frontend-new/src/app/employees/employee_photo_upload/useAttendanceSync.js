import { useState, useEffect } from 'react';

export function useAttendanceSync(initialData = []) {
  const [availableEmployees, setAvailableEmployees] = useState([]);
  const [selectedEmployees, setSelectedEmployees] = useState([]);
  const [checkedItems, setCheckedItems] = useState([]);
  const [searchTerm, setSearchTerm] = useState('');

  // IMPORTANT: This syncs the hook when the API data arrives
  useEffect(() => {
    if (initialData && initialData.length > 0 && availableEmployees.length === 0) {
      setAvailableEmployees(initialData);
      setCheckedItems([]);
      setSelectedEmployees([]);
    }
  }, [initialData, availableEmployees.length]);

  const toggleCheck = (id) => {
    setCheckedItems(prev =>
      prev.includes(id) ? prev.filter(i => i !== id) : [...prev, id]
    );
  };

  const moveSelectedToRight = () => {
    const toMove = availableEmployees.filter(emp => checkedItems.includes(emp.id));
    if (toMove.length === 0) return;
    setSelectedEmployees(prev => [...prev, ...toMove]);
    setAvailableEmployees(prev => prev.filter(emp => !checkedItems.includes(emp.id)));
    setCheckedItems([]);
  };

  const moveSelectedToLeft = () => {
    const toMove = selectedEmployees.filter(emp => checkedItems.includes(emp.id));
    if (toMove.length === 0) return;
    setAvailableEmployees(prev => [...prev, ...toMove]);
    setSelectedEmployees(prev => prev.filter(emp => !checkedItems.includes(emp.id)));
    setCheckedItems([]);
  };

  const moveAllToRight = () => {
    setSelectedEmployees(prev => [...prev, ...availableEmployees]);
    setAvailableEmployees([]);
    setCheckedItems([]);
  };

  const moveAllToLeft = () => {
    setAvailableEmployees(prev => [...prev, ...selectedEmployees]);
    setSelectedEmployees([]);
    setCheckedItems([]);
  };

  const filteredAvailable = availableEmployees.filter(emp =>
    emp.name?.toLowerCase().includes(searchTerm.toLowerCase()) ||
    emp.itemId?.toString().includes(searchTerm)
  );

  return {
    available: filteredAvailable,
    selected: selectedEmployees,
    checkedItems,
    searchTerm,
    setSearchTerm,
    toggleCheck,
    moveSelectedToRight,
    moveSelectedToLeft,
    moveAllToRight,
    moveAllToLeft
  };
}