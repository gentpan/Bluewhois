/**
 * WHOIS查询工具 - UI增强功能
 * 包含：JSON安全高亮、查询结果美化、日期转换、状态标签处理等
 */

// ==================== JSON安全高亮 ====================
// 安全覆盖 JSON 语法高亮，先转义再上色，防止 XSS 注入
(function () {
  function highlightSafe(jsonStr) {
    try {
      const obj = typeof jsonStr === "string" ? JSON.parse(jsonStr) : jsonStr;
      const formatted = JSON.stringify(obj, null, 2);
      // 先转义
      const escaped = formatted
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;");
      // 再做简单着色（基于已转义文本）
      const colored = escaped
        .replace(
          /(&quot;(?:[^&]|&quot;|\\)*&quot;)(\s*):/g,
          '<span class="json-key">$1</span>$2:'
        )
        .replace(
          /:(\s*)(&quot;(?:[^&]|&quot;|\\)*&quot;)/g,
          ':$1<span class="json-string">$2</span>'
        )
        .replace(
          /:(\s*)(-?\d+(?:\.\d+)?(?:[eE][+-]?\d+)?)/g,
          ':$1<span class="json-number">$2</span>'
        )
        .replace(
          /:(\s*)(true|false)/g,
          ':$1<span class="json-boolean">$2</span>'
        )
        .replace(/:(\s*)(null)/g, ':$1<span class="json-null">$2</span>')
        .replace(/([{}[\],:])/g, '<span class="json-punctuation">$1</span>');
      return colored;
    } catch (e) {
      const safe = (jsonStr || "")
        .toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;");
      return safe;
    }
  }
  // 覆盖全局函数，确保后加载时优先生效
  try {
    window.highlightJsonSyntax = highlightSafe;
  } catch (_) {}
})();

// 查询结果美化增强脚本
(function() {
    'use strict';
    
    // 监听 DOM 变化，美化查询结果
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1 && node.id === 'query-result') {
                        enhanceResultDisplay(node);
                    } else if (node.nodeType === 1 && node.querySelector && node.querySelector('#query-result')) {
                        enhanceResultDisplay(node.querySelector('#query-result'));
                    }
                });
            }
            
            // 检查现有内容
            const resultContainer = document.getElementById('query-result');
            if (resultContainer && resultContainer.innerHTML && !resultContainer.classList.contains('enhanced')) {
                enhanceResultDisplay(resultContainer);
                resultContainer.classList.add('enhanced');
            }
        });
    });
    
    // 开始观察
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            observeResultContainer();
        });
    } else {
        observeResultContainer();
    }
    
    function observeResultContainer() {
        const resultContainer = document.getElementById('query-result');
        if (resultContainer) {
            observer.observe(resultContainer, {
                childList: true,
                subtree: true
            });
            
            // 立即处理现有内容
            if (resultContainer.innerHTML) {
                enhanceResultDisplay(resultContainer);
                resultContainer.classList.add('enhanced');
            }
        }
    }
    
    function enhanceResultDisplay(container) {
        if (!container) return;
        
        // 美化状态标签
        enhanceStatusBadges(container);
        
        // 美化卡片结构
        enhanceCardStructure(container);
        
        // 添加联系人信息
        enhanceContactInfo(container);
    }
    
    // 状态翻译映射
    const statusMap = {
        'ok': {cn: '正常', en: 'OK', desc: '域名状态正常'},
        'active': {cn: '活跃', en: 'Active', desc: '域名处于活跃状态'},
        'inactive': {cn: '非活跃', en: 'Inactive', desc: '域名处于非活跃状态'},
        'clientdeleteprohibited': {cn: '禁止客户端删除', en: 'Client Delete Prohibited', desc: '域名禁止通过客户端删除，防止误操作'},
        'clienttransferprohibited': {cn: '禁止客户端转移', en: 'Client Transfer Prohibited', desc: '域名禁止通过客户端转移注册商，保护域名安全'},
        'clientupdateprohibited': {cn: '禁止客户端更新', en: 'Client Update Prohibited', desc: '域名信息禁止通过客户端更新，防止未经授权的修改'},
        'serverdeleteprohibited': {cn: '禁止服务器删除', en: 'Server Delete Prohibited', desc: '域名禁止服务器端删除，提供额外的安全保护'},
        'servertransferprohibited': {cn: '禁止服务器转移', en: 'Server Transfer Prohibited', desc: '域名禁止服务器端转移，防止未授权的注册商变更'},
        'serverupdateprohibited': {cn: '禁止服务器更新', en: 'Server Update Prohibited', desc: '域名信息禁止服务器端更新，确保注册信息稳定性'},
        'clientrenewprohibited': {cn: '禁止客户端续费', en: 'Client Renew Prohibited', desc: '域名禁止通过客户端续费，保护域名安全'},
        'pendingdelete': {cn: '待删除', en: 'Pending Delete', desc: '域名已标记为待删除状态，即将被释放'},
        'pendingtransfer': {cn: '待转移', en: 'Pending Transfer', desc: '域名转移请求已提交，正在处理中'},
        'pendingrestore': {cn: '待恢复', en: 'Pending Restore', desc: '域名恢复请求已提交，正在处理中'},
        'pendingverification': {cn: '待验证', en: 'Pending Verification', desc: '域名信息待验证'},
        'pendingcreate': {cn: '待创建', en: 'Pending Create', desc: '域名创建请求待处理'},
        'pendingrenew': {cn: '待续费', en: 'Pending Renew', desc: '域名续费请求待处理'},
        'redemptionperiod': {cn: '赎回期', en: 'Redemption Period', desc: '域名处于赎回期，可以支付费用恢复域名'},
        'clienthold': {cn: '客户端锁定', en: 'Client Hold', desc: '域名被客户端锁定，可能因欠费或其他原因暂停解析'},
        'serverhold': {cn: '服务器锁定', en: 'Server Hold', desc: '域名被服务器端锁定，通常因违规或争议暂停解析'},
        'addperiod': {cn: '添加期', en: 'Add Period', desc: '域名处于添加期'},
        'autorenewperiod': {cn: '自动续费期', en: 'Auto Renew Period', desc: '域名处于自动续费期'},
        'renewperiod': {cn: '续费期', en: 'Renew Period', desc: '域名处于续费期'},
        'transferperiod': {cn: '转移期', en: 'Transfer Period', desc: '域名处于转移期'}
    };
    
    // 确定状态类型
    function getStatusClass(statusText) {
        const lower = statusText.toLowerCase();
        
        // 精确匹配
        for (const key in statusMap) {
            if (lower.includes(key)) {
                return key;
            }
        }
        
        // 中文匹配
        if (lower.includes('禁止') && lower.includes('删除')) {
            if (lower.includes('服务器')) return 'serverdeleteprohibited';
            return 'clientdeleteprohibited';
        }
        if (lower.includes('禁止') && lower.includes('转移')) {
            if (lower.includes('服务器')) return 'servertransferprohibited';
            return 'clienttransferprohibited';
        }
        if (lower.includes('禁止') && lower.includes('更新')) {
            if (lower.includes('服务器')) return 'serverupdateprohibited';
            return 'clientupdateprohibited';
        }
        if (lower.includes('禁止') && lower.includes('续费')) {
            return 'clientrenewprohibited';
        }
        if (lower.includes('待') && lower.includes('删除')) return 'pendingdelete';
        if (lower.includes('待') && lower.includes('转移')) return 'pendingtransfer';
        if (lower.includes('待') && lower.includes('恢复')) return 'pendingrestore';
        if (lower.includes('待') && lower.includes('验证')) return 'pendingverification';
        if (lower.includes('待') && lower.includes('创建')) return 'pendingcreate';
        if (lower.includes('待') && lower.includes('续费')) return 'pendingrenew';
        if (lower.includes('赎回')) return 'redemptionperiod';
        if (lower.includes('客户端') && lower.includes('锁定')) return 'clienthold';
        if (lower.includes('服务器') && lower.includes('锁定')) return 'serverhold';
        if (lower.includes('添加期')) return 'addperiod';
        if (lower.includes('自动续费期')) return 'autorenewperiod';
        if (lower.includes('续费期')) return 'renewperiod';
        if (lower.includes('转移期')) return 'transferperiod';
        if (lower.includes('正常') || lower === 'ok') return 'ok';
        if (lower.includes('活跃') || lower === 'active') return 'active';
        if (lower.includes('非活跃') || lower === 'inactive') return 'inactive';
        
        return 'inactive';
    }
    
    // 美化状态标签为新的中英文标签式
    function enhanceStatusBadges(container) {
        // 查找状态容器 - 查找包含"状态:"的div
        const statusSections = Array.from(container.querySelectorAll('div')).filter(div => {
            const text = div.textContent || '';
            return text.includes('状态') || text.includes('Status') || text.includes('status');
        });
        
        statusSections.forEach(function(statusSection) {
            // 查找所有旧的状态标签
            const oldBadges = statusSection.querySelectorAll('span[class*="inline-flex"], span[class*="bg-amber"], span[class*="bg-yellow"], span[class*="bg-green"], span[class*="bg-blue"], span[class*="bg-red"], span[class*="bg-purple"], span.group, span[class*="group"]');
            
            if (oldBadges.length === 0) return;
            
            // 查找状态容器
            let statusContainer = statusSection.querySelector('div.flex.flex-wrap, div[class*="flex"]');
            if (!statusContainer) {
                // 创建状态容器
                statusContainer = document.createElement('div');
                statusContainer.className = 'status-badge-group';
                
                // 找到包含标签的父元素
                const parent = oldBadges[0].parentNode;
                if (parent) {
                    // 清空并添加新容器
                    parent.innerHTML = '';
                    parent.appendChild(statusContainer);
                }
            } else {
                statusContainer.className = 'status-badge-group';
            }
            
            oldBadges.forEach(function(badge) {
                if (badge.classList.contains('status-badge-enhanced') || badge.classList.contains('status-badge')) return;
                
                // 检查是否包含状态相关的内容
                const textContent = badge.textContent.trim();
                const hasStatusIndicators = /(正常|活跃|禁止|待|锁定|赎回|删除|转移|更新|续费|ok|active|prohibited|pending|hold|redemption|delete|transfer|update|renew)/i.test(textContent);
                
                if (!hasStatusIndicators && !badge.querySelector('i.fa-info-circle')) return;
                
                // 从子元素获取中文和英文
                const cnSpan = badge.querySelector('span.font-semibold, span[class*="font-semibold"], span.text-sm');
                const enSpan = badge.querySelector('span[class*="opacity"], span.text-\\[10px\\], span.text-xs');
                
                let cn = '';
                let en = '';
                let title = badge.getAttribute('title') || '';
                
                // 从 tooltip 获取描述
                const tooltip = badge.querySelector('div[class*="bg-gray-900"], div[class*="absolute"]');
                if (tooltip) {
                    const tooltipDesc = tooltip.querySelector('div:last-child');
                    if (tooltipDesc) {
                        title = tooltipDesc.textContent.trim();
                    }
                }
                
                if (cnSpan) {
                    cn = cnSpan.textContent.trim();
                } else {
                    // 尝试从文本中提取中文
                    const cnMatch = textContent.match(/^([^（(]+)/);
                    cn = cnMatch ? cnMatch[1].trim() : '';
                }
                
                if (enSpan) {
                    en = enSpan.textContent.replace(/[()]/g, '').trim();
                } else {
                    // 尝试从文本中提取英文
                    const enMatch = textContent.match(/[（(]([^）)]+)[）)]/);
                    en = enMatch ? enMatch[1].trim() : '';
                }
                
                // 处理多个状态（文本中可能包含多个状态关键词，用空格分隔）
                const fullText = (cn + ' ' + en + ' ' + textContent).toLowerCase();
                
                // 识别所有可能的状态关键词
                const allStatusKeys = Object.keys(statusMap);
                const foundStatuses = allStatusKeys.filter(key => {
                    const statusLower = key.toLowerCase();
                    return fullText.includes(statusLower) || 
                           (cn && cn.toLowerCase().includes(statusLower)) ||
                           (en && en.toLowerCase().includes(statusLower)) ||
                           (textContent && textContent.toLowerCase().includes(statusLower));
                });
                
                if (foundStatuses.length > 1) {
                    // 多个状态，为每个状态创建独立标签
                    foundStatuses.forEach(function(statusKey) {
                        const statusInfo = statusMap[statusKey] || {cn: statusKey, en: statusKey, desc: '状态信息'};
                        
                        const newBadge = document.createElement('span');
                        newBadge.className = 'status-badge status-badge-enhanced ' + statusKey;
                        newBadge.innerHTML = `
                            <span class="status-badge-label-cn">${escapeHtml(statusInfo.cn)}</span>
                            <span class="status-badge-label-en">(${escapeHtml(statusInfo.en)})</span>
                            <i class="fas fa-info-circle status-badge-icon"></i>
                            <div class="status-tooltip">
                                <div class="status-tooltip-title">${escapeHtml(statusInfo.cn)}</div>
                                <div class="status-tooltip-desc">${escapeHtml(statusInfo.desc)}</div>
                            </div>
                        `;
                        statusContainer.appendChild(newBadge);
                    });
                    
                    badge.remove();
                } else if (foundStatuses.length === 1) {
                    // 单个状态
                    const statusKey = foundStatuses[0];
                    const statusInfo = statusMap[statusKey];
                    
                    const newBadge = document.createElement('span');
                    newBadge.className = 'status-badge status-badge-enhanced ' + statusKey;
                    
                    const displayCn = cn || statusInfo.cn || '未知状态';
                    const displayEn = en || statusInfo.en || '';
                    
                    newBadge.innerHTML = `
                        <span class="status-badge-label-cn">${escapeHtml(displayCn)}</span>
                        ${displayEn && displayEn !== displayCn ? `<span class="status-badge-label-en">(${escapeHtml(displayEn)})</span>` : ''}
                        <i class="fas fa-info-circle status-badge-icon"></i>
                        <div class="status-tooltip">
                            <div class="status-tooltip-title">${escapeHtml(displayCn)}</div>
                            <div class="status-tooltip-desc">${escapeHtml(title || statusInfo.desc)}</div>
                        </div>
                    `;
                    
                    badge.parentNode.replaceChild(newBadge, badge);
                } else {
                    // 无法识别状态，使用默认样式
                    const statusText = (cn + ' ' + en + ' ' + textContent).toLowerCase();
                    const statusKey = getStatusClass(statusText);
                    const statusInfo = statusMap[statusKey] || {cn: cn || '未知状态', en: en || 'Unknown', desc: title || '状态信息'};
                    
                    const newBadge = document.createElement('span');
                    newBadge.className = 'status-badge status-badge-enhanced ' + statusKey;
                    
                    const displayCn = cn || statusInfo.cn;
                    const displayEn = (en && en !== displayCn) ? en : (statusInfo.en !== statusInfo.cn ? statusInfo.en : '');
                    
                    newBadge.innerHTML = `
                        <span class="status-badge-label-cn">${escapeHtml(displayCn)}</span>
                        ${displayEn ? `<span class="status-badge-label-en">(${escapeHtml(displayEn)})</span>` : ''}
                        <i class="fas fa-info-circle status-badge-icon"></i>
                        <div class="status-tooltip">
                            <div class="status-tooltip-title">${escapeHtml(displayCn)}</div>
                            <div class="status-tooltip-desc">${escapeHtml(title || statusInfo.desc)}</div>
                        </div>
                    `;
                    
                    badge.parentNode.replaceChild(newBadge, badge);
                }
            });
        });
    }
    
    // 美化卡片结构
    function enhanceCardStructure(container) {
        const cards = container.querySelectorAll('div.bg-white.rounded-2xl, div[class*="bg-white"][class*="rounded"]');
        
        cards.forEach(function(card) {
            if (card.classList.contains('result-card-enhanced')) return;
            
            // 查找标题区域
            const header = card.querySelector('div.flex.items-center.justify-center.p-4, div[class*="border-b"][class*="bg-gray"]');
            const body = card.querySelector('div.p-6, div.p-8');
            
            if (header && body) {
                // 创建新的卡片结构
                const newCard = document.createElement('div');
                newCard.className = 'result-card result-card-enhanced';
                
                // 创建头部
                const cardHeader = document.createElement('div');
                cardHeader.className = 'result-card-header';
                
                const headerContent = document.createElement('div');
                headerContent.className = 'result-card-header-content';
                
                // 提取标题内容
                const titleText = header.textContent.trim();
                const domainMatch = titleText.match(/^([A-Z0-9.-]+)/i);
                const domain = domainMatch ? domainMatch[1] : titleText;
                
                // 提取头部中的徽章和标签
                const headerHTML = header.innerHTML;
                const badges = header.querySelectorAll('span[class*="px-2"], span[class*="py-1"], span[class*="bg-"]');
                let badgesHTML = '';
                badges.forEach(badge => {
                    if (badge.textContent.trim() && !badge.textContent.includes(domain)) {
                        badgesHTML += badge.outerHTML;
                    }
                });
                
                // 提取其他信息（如时间等）
                const otherInfo = header.querySelectorAll('span.text-xs, span.text-gray-500');
                let otherInfoHTML = '';
                otherInfo.forEach(info => {
                    if (info.textContent.trim() && !info.textContent.includes(domain)) {
                        otherInfoHTML += `<span class="result-card-quick-info-item">${info.textContent.trim()}</span>`;
                    }
                });
                
                headerContent.innerHTML = `
                    <div class="result-card-title">
                        <div class="result-card-domain">${escapeHtml(domain.toUpperCase())}</div>
                        ${badgesHTML ? `<div class="result-card-meta">${badgesHTML}</div>` : ''}
                    </div>
                    ${otherInfoHTML ? `<div class="result-card-quick-info">${otherInfoHTML}</div>` : ''}
                `;
                
                cardHeader.appendChild(headerContent);
                
                // 创建主体
                const cardBody = document.createElement('div');
                cardBody.className = 'result-card-body';
                cardBody.innerHTML = body.innerHTML;
                
                newCard.appendChild(cardHeader);
                newCard.appendChild(cardBody);
                
                // 添加 JSON 部分（如果存在）
                const jsonSection = card.querySelector('div.border-t.border-gray-200');
                if (jsonSection) {
                    const jsonWrapper = document.createElement('div');
                    jsonWrapper.className = 'result-card-body';
                    jsonWrapper.style.borderTop = '1px solid var(--border-color)';
                    jsonWrapper.style.marginTop = '0';
                    jsonWrapper.style.paddingTop = '24px';
                    jsonWrapper.innerHTML = jsonSection.innerHTML;
                    newCard.appendChild(jsonWrapper);
                }
                
                card.parentNode.replaceChild(newCard, card);
            }
        });
    }
    
    // 添加联系人信息显示
    function enhanceContactInfo(container) {
        // 查找联系人数据（从 JSON 或现有数据中提取）
        const contactSection = container.querySelector('.contact-info-card');
        if (contactSection) return; // 已存在
        
        // 尝试从 window.rdapJsonData 获取联系人信息
        let contacts = [];
        
        if (window.rdapJsonData) {
            try {
                const data = typeof window.rdapJsonData === 'string' ? JSON.parse(window.rdapJsonData) : window.rdapJsonData;
                contacts = data.contacts || [];
            } catch (e) {
                console.error('解析联系人信息失败:', e);
            }
        }
        
        // 如果没有联系人数据，尝试从页面中提取
        if (contacts.length === 0) {
            // 查找已有的联系人信息显示
            const body = container.querySelector('.result-card-body, .p-6, .p-8, div[class*="space-y"]');
            if (body) {
                // 使用Array.filter而不是:contains选择器（不支持）
                const allDivs = Array.from(body.querySelectorAll('div'));
                const contactTexts = allDivs.filter(div => {
                    const text = div.textContent || '';
                    return text.includes('注册人') || text.includes('联系人') || text.includes('注册商');
                });
                // 这里可以进一步提取结构化信息
            }
        }
        
        if (contacts.length > 0) {
            const body = container.querySelector('.result-card-body, .p-6, .p-8, div[class*="space-y"]');
            if (body) {
                // 查找是否已有联系人信息显示
                const hasContactInfo = body.textContent.includes('注册人') || body.textContent.includes('联系人');
                const existingContactCard = body.querySelector('.contact-info-card');
                
                if (!existingContactCard && !hasContactInfo) {
                    const contactCard = document.createElement('div');
                    contactCard.className = 'contact-info-card';
                    contactCard.innerHTML = `
                        <div class="contact-info-title">
                            <i class="fas fa-user-circle"></i>
                            <span>联系人信息</span>
                        </div>
                    `;
                    
                    const processedTypes = new Set();
                    
                    contacts.forEach(function(contact) {
                        if (!contact || processedTypes.has(contact.type)) return;
                        
                        const typeNames = {
                            'registrant': '注册人',
                            'registrar': '注册商联系人',
                            'administrative': '管理联系人',
                            'technical': '技术联系人',
                            'billing': '账单联系人'
                        };
                        
                        const typeName = typeNames[contact.type] || contact.type;
                        
                        // 检查是否有有效信息
                        const hasInfo = contact.name || contact.organization || contact.email || contact.phone || contact.address;
                        if (!hasInfo) return;
                        
                        processedTypes.add(contact.type);
                        
                        const contactItem = document.createElement('div');
                        contactItem.className = 'contact-info-item';
                        
                        let contactHtml = `<div class="contact-info-label">${typeName}:</div><div class="contact-info-value">`;
                        
                        if (contact.name) {
                            const nameLabel = contact.type === 'registrar' ? '名称' : '姓名';
                            contactHtml += `<div><strong>${nameLabel}:</strong> ${escapeHtml(contact.name)}</div>`;
                        }
                        if (contact.organization) {
                            contactHtml += `<div><strong>组织:</strong> ${escapeHtml(contact.organization)}</div>`;
                        }
                        if (contact.email) {
                            contactHtml += `<div><strong>邮箱:</strong> <a href="mailto:${escapeHtml(contact.email)}" style="color: var(--accent-black);">${escapeHtml(contact.email)}</a></div>`;
                        }
                        if (contact.phone) {
                            contactHtml += `<div><strong>电话:</strong> ${escapeHtml(contact.phone)}</div>`;
                        }
                        if (contact.fax) {
                            contactHtml += `<div><strong>传真:</strong> ${escapeHtml(contact.fax)}</div>`;
                        }
                        if (contact.address) {
                            const address = Array.isArray(contact.address) ? contact.address.join(', ') : contact.address;
                            contactHtml += `<div><strong>地址:</strong> ${escapeHtml(address)}</div>`;
                        }
                        if (contact.city) {
                            contactHtml += `<div><strong>城市:</strong> ${escapeHtml(contact.city)}</div>`;
                        }
                        if (contact.state || contact.province) {
                            contactHtml += `<div><strong>省份:</strong> ${escapeHtml(contact.state || contact.province)}</div>`;
                        }
                        if (contact.postal_code || contact.zip) {
                            contactHtml += `<div><strong>邮编:</strong> ${escapeHtml(contact.postal_code || contact.zip)}</div>`;
                        }
                        if (contact.country) {
                            contactHtml += `<div><strong>国家:</strong> ${escapeHtml(contact.country)}</div>`;
                        }
                        
                        contactHtml += '</div>';
                        contactItem.innerHTML = contactHtml;
                        contactCard.appendChild(contactItem);
                    });
                    
                    // 只有在有联系人信息时才插入
                    if (contactCard.querySelectorAll('.contact-info-item').length > 0) {
                        // 查找插入位置 - 在域名服务器或 DNSSEC 之前
                        const bodyDivs = Array.from(body.querySelectorAll('div'));
                        let insertBefore = null;
                        
                        for (let i = 0; i < bodyDivs.length; i++) {
                            const div = bodyDivs[i];
                            if (div.textContent.includes('域名服务器') || div.textContent.includes('DNSSEC') || div.textContent.includes('Nameservers')) {
                                insertBefore = div;
                                break;
                            }
                        }
                        
                        if (insertBefore && insertBefore.parentNode) {
                            insertBefore.parentNode.insertBefore(contactCard, insertBefore);
                        } else {
                            // 如果没有找到插入点，添加到空间区域之后
                            const spaceDiv = body.querySelector('div.space-y-4, div[class*="space-y"]');
                            if (spaceDiv && spaceDiv.parentNode) {
                                spaceDiv.parentNode.insertBefore(contactCard, spaceDiv.nextSibling);
                            } else {
                                body.appendChild(contactCard);
                            }
                        }
                    }
                }
            }
        }
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // 重写 displayWhoisResult 以使用新的样式
    const originalDisplay = window.displayWhoisResult;
    if (originalDisplay) {
        window.displayWhoisResult = function(...args) {
            originalDisplay.apply(this, args);
            setTimeout(function() {
                const resultContainer = document.getElementById('query-result');
                if (resultContainer) {
                    enhanceResultDisplay(resultContainer);
                    resultContainer.classList.add('enhanced');
                }
            }, 100);
        };
    }
})();


(function() {
    'use strict';
    
    // 将UTC时间转换为UTC+8（北京时间）
    function convertToUTC8(dateStr) {
        if (!dateStr) return '';
        
        try {
            // 移除UTC字符串
            let cleanDateStr = dateStr.replace(/\s*UTC.*$/i, '').trim();
            
            // 解析日期字符串，支持单数字月和日（如 1995/5/3）
            // 格式：YYYY/M/D HH:MM:SS 或 YYYY/MM/DD HH:MM:SS
            const datePattern = /^(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})(?:\s+(\d{1,2}):(\d{1,2}):(\d{1,2}))?/;
            const match = cleanDateStr.match(datePattern);
            
            if (match) {
                const year = parseInt(match[1], 10);
                const month = parseInt(match[2], 10) - 1; // JavaScript月份从0开始
                const day = parseInt(match[3], 10);
                const hours = match[4] ? parseInt(match[4], 10) : 0;
                const minutes = match[5] ? parseInt(match[5], 10) : 0;
                const seconds = match[6] ? parseInt(match[6], 10) : 0;
                
                // 创建UTC日期对象
                const date = new Date(Date.UTC(year, month, day, hours, minutes, seconds));
                
                // 检查日期是否有效
                if (isNaN(date.getTime())) {
                    return dateStr;
                }
                
                // 转换为UTC+8（加上8小时）
                const utc8Time = date.getTime() + 8 * 60 * 60 * 1000;
                const utc8Date = new Date(utc8Time);
                
                // 格式化为统一格式：YYYY/MM/DD HH:MM:SS（补零）
                const formattedYear = utc8Date.getUTCFullYear();
                const formattedMonth = String(utc8Date.getUTCMonth() + 1).padStart(2, '0');
                const formattedDay = String(utc8Date.getUTCDate()).padStart(2, '0');
                const formattedHours = String(utc8Date.getUTCHours()).padStart(2, '0');
                const formattedMinutes = String(utc8Date.getUTCMinutes()).padStart(2, '0');
                const formattedSeconds = String(utc8Date.getUTCSeconds()).padStart(2, '0');
                
                return `${formattedYear}/${formattedMonth}/${formattedDay} ${formattedHours}:${formattedMinutes}:${formattedSeconds}`;
            }
            
            // 如果正则匹配失败，尝试其他格式
            let date;
            if (cleanDateStr.includes('T')) {
                // ISO 8601 格式
                if (!cleanDateStr.match(/[+-]\d{2}:?\d{2}$/) && !cleanDateStr.endsWith('Z')) {
                    cleanDateStr = cleanDateStr + 'Z';
                }
                date = new Date(cleanDateStr);
            } else {
                // 尝试直接解析
                date = new Date(cleanDateStr);
            }
            
            // 检查日期是否有效
            if (isNaN(date.getTime())) {
                return dateStr;
            }
            
            // 转换为UTC+8
            const utc8Time = date.getTime() + 8 * 60 * 60 * 1000;
            const utc8Date = new Date(utc8Time);
            
            // 格式化
            const year = utc8Date.getUTCFullYear();
            const month = String(utc8Date.getUTCMonth() + 1).padStart(2, '0');
            const day = String(utc8Date.getUTCDate()).padStart(2, '0');
            const hours = String(utc8Date.getUTCHours()).padStart(2, '0');
            const minutes = String(utc8Date.getUTCMinutes()).padStart(2, '0');
            const seconds = String(utc8Date.getUTCSeconds()).padStart(2, '0');
            
            return `${year}/${month}/${day} ${hours}:${minutes}:${seconds}`;
        } catch (e) {
            console.error('日期转换错误:', e, dateStr);
            return dateStr;
        }
    }
    
    // 增强日期显示
    function enhanceDateDisplay(container) {
        // 查找所有包含日期的div
        const dateDivs = Array.from(container.querySelectorAll('div'));
        
        dateDivs.forEach(function(div) {
            const text = div.textContent || '';
            
            // 检查是否是日期相关的行
            const isDateRow = text.includes('创建日期') || 
                            text.includes('更新日期') || 
                            text.includes('过期日期') ||
                            text.includes('创建日期:') || 
                            text.includes('更新日期:') || 
                            text.includes('过期日期:');
            
            if (!isDateRow) return;
            
            // 检查是否已经处理过（避免重复处理）
            if (div.querySelector('.date-display-wrapper')) return;
            
            // 查找日期值 - 使用正则表达式在整个div中查找
            const datePattern = /(\d{4}[-\/]\d{1,2}[-\/]\d{1,2}(?:\s+\d{1,2}:\d{1,2}:\d{1,2})?\s*UTC)/i;
            const dateMatch = text.match(datePattern);
            
            if (dateMatch) {
                const dateValue = dateMatch[1];
                
                // 提取原始日期（去掉UTC部分）
                const originalDate = dateValue.replace(/\s*UTC.*$/i, '').trim();
                
                // 转换为UTC+8
                const utc8Date = convertToUTC8(originalDate);
                
                if (utc8Date) {
                    // 创建新的日期显示结构（统一显示UTC+8标注）
                    const dateWrapper = document.createElement('div');
                    dateWrapper.className = 'date-display-wrapper';
                    
                    const dateTimeSpan = document.createElement('span');
                    dateTimeSpan.className = 'date-time-value';
                    dateTimeSpan.textContent = utc8Date;
                    
                    const utcBadge = document.createElement('span');
                    utcBadge.className = 'utc-badge';
                    utcBadge.textContent = 'UTC+8';
                    
                    dateWrapper.appendChild(dateTimeSpan);
                    dateWrapper.appendChild(utcBadge);
                    
                    // 替换div中的日期文本
                    const divContent = div.innerHTML;
                    if (divContent.includes(dateValue)) {
                        div.innerHTML = divContent.replace(datePattern, dateWrapper.outerHTML);
                    }
                }
            }
        });
    }
    
    // 监听DOM变化，增强日期显示
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) {
                        const resultContainer = node.id === 'query-result' ? node : 
                                              (node.querySelector ? node.querySelector('#query-result') : null);
                        if (resultContainer) {
                            setTimeout(function() {
                                enhanceDateDisplay(resultContainer);
                            }, 100);
                        }
                    }
                });
            }
        });
    });
    
    // 初始化
    function init() {
        // 观察查询结果容器
        const resultContainer = document.getElementById('query-result');
        if (resultContainer) {
            enhanceDateDisplay(resultContainer);
        }
        
        // 开始观察
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }
    
    // DOM加载完成后初始化
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
    // 暴露函数供其他脚本调用
    window.enhanceDateDisplay = enhanceDateDisplay;
})();

// 域名自动补全功能
(function () {
  "use strict";

  // 常见域名后缀列表
  const commonTLDs = [
    "com",
    "net",
    "io",
    "ai",
    "cc",
    "org",
    "co",
    "me",
    "tv",
    "xyz",
    "site",
    "online",
    "info",
    "dev",
    "app",
    "tech",
    "shop",
    "store",
    "cloud",
    "space",
    "link",
    "blog",
    "news",
    "top",
    "vip",
    "win",
    "fun",
    "live",
    "club",
  ];

  // 域名自动补全功能
  function initDomainAutocomplete() {
    const domainInput = document.getElementById("domain-input");
    const suggestionsDiv = document.getElementById("domain-suggestions");

    if (!domainInput || !suggestionsDiv) return;

    let currentInput = "";
    let selectedIndex = -1;

    domainInput.addEventListener("input", function (e) {
      currentInput = e.target.value.trim();
      selectedIndex = -1;

      // 如果输入为空或已包含域名后缀，隐藏建议
      if (!currentInput || currentInput.includes(".")) {
        suggestionsDiv.classList.add("hidden");
        return;
      }

      // 生成建议列表
      const suggestions = commonTLDs.map((tld) => ({
        domain: currentInput + "." + tld,
        tld: tld,
      }));

      displaySuggestions(suggestions);
    });

    domainInput.addEventListener("keydown", function (e) {
      if (!suggestionsDiv.classList.contains("hidden")) {
        const items = suggestionsDiv.querySelectorAll(".suggestion-item");

        if (e.key === "ArrowDown") {
          e.preventDefault();
          selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
          updateSelection(items);
        } else if (e.key === "ArrowUp") {
          e.preventDefault();
          selectedIndex = Math.max(selectedIndex - 1, -1);
          updateSelection(items);
        } else if (e.key === "Enter" && selectedIndex >= 0) {
          e.preventDefault();
          items[selectedIndex].click();
        } else if (e.key === "Escape") {
          suggestionsDiv.classList.add("hidden");
          selectedIndex = -1;
        }
      }
    });

    // 点击外部关闭建议
    document.addEventListener("click", function (e) {
      if (
        !domainInput.contains(e.target) &&
        !suggestionsDiv.contains(e.target)
      ) {
        suggestionsDiv.classList.add("hidden");
      }
    });

    // 保证滚轮优先滚动下拉框，避免事件冒泡到页面导致“看起来无法滚动”
    suggestionsDiv.addEventListener(
      "wheel",
      function (e) {
        if (suggestionsDiv.classList.contains("hidden")) return;
        if (suggestionsDiv.scrollHeight <= suggestionsDiv.clientHeight) return;

        const atTop = suggestionsDiv.scrollTop <= 0;
        const atBottom =
          Math.ceil(suggestionsDiv.scrollTop + suggestionsDiv.clientHeight) >=
          suggestionsDiv.scrollHeight;
        const scrollingDown = e.deltaY > 0;

        // 在边界时把滚轮交还给页面，避免页面“滚不动”
        if ((!scrollingDown && atTop) || (scrollingDown && atBottom)) {
          return;
        }
        // 仅在下拉内部可滚动时消费事件
        e.preventDefault();
        e.stopPropagation();
      },
      { passive: false }
    );

    function displaySuggestions(suggestions) {
      suggestionsDiv.innerHTML = "";

      if (suggestions.length === 0) {
        suggestionsDiv.classList.add("hidden");
        return;
      }

      suggestions.forEach((suggestion, index) => {
        const item = document.createElement("div");
        item.className = "suggestion-item";
        item.dataset.domain = suggestion.domain;

        item.innerHTML = `
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-weight: 500; color: var(--text-primary);">${suggestion.domain}</span>
                        <span style="font-size: 11px; color: var(--text-tertiary); background: var(--bg-tertiary); padding: 2px 8px; border-radius: 4px;">.${suggestion.tld}</span>
                    </div>
                    <i class="fas fa-chevron-right" style="font-size: 10px; color: var(--text-tertiary);"></i>
                `;

        item.addEventListener("click", function () {
          domainInput.value = suggestion.domain;
          suggestionsDiv.classList.add("hidden");
          // 自动提交查询
          const form = domainInput.closest(".query-form");
          if (form && window.queryDomain) {
            queryDomain(suggestion.domain);
          }
        });

        suggestionsDiv.appendChild(item);
      });

      suggestionsDiv.classList.remove("hidden");
    }

    function updateSelection(items) {
      items.forEach((item, index) => {
        if (index === selectedIndex) {
          item.style.background = "var(--bg-tertiary)";
        } else {
          item.style.background = "";
        }
      });
    }
  }

  // 查询按钮 loading 状态控制
  function setQueryButtonLoading(loading) {
    const forms = document.querySelectorAll(".query-form");
    forms.forEach((form) => {
      const button = form.querySelector('button[type="submit"]');
      if (button) {
        const content = button.querySelector(".query-button-content");
        const loadingEl = button.querySelector(".query-button-loading");
        if (content && loadingEl) {
          if (loading) {
            content.classList.add("hidden");
            loadingEl.classList.remove("hidden");
            button.disabled = true;
          } else {
            content.classList.remove("hidden");
            loadingEl.classList.add("hidden");
            button.disabled = false;
          }
        }
      }
    });
  }

  // 初始化功能
  document.addEventListener("DOMContentLoaded", function () {
    // 初始化域名自动补全
    initDomainAutocomplete();

    // 拦截 showLoading 和 hideLoading（备用方案）
    function wrapLoadingFunction(funcName, addLoading) {
      let checkCount = 0;
      const maxChecks = 100;

      function checkAndWrap() {
        if (window.NetworkQueryTool && window.NetworkQueryTool[funcName]) {
          const originalFunc = window.NetworkQueryTool[funcName];
          window.NetworkQueryTool[funcName] = function () {
            setQueryButtonLoading(addLoading);
            return originalFunc.apply(this, arguments);
          };
        } else if (window[funcName]) {
          const originalFunc = window[funcName];
          window[funcName] = function () {
            setQueryButtonLoading(addLoading);
            return originalFunc.apply(this, arguments);
          };
        } else if (checkCount < maxChecks) {
          checkCount++;
          setTimeout(checkAndWrap, 50);
        }
      }

      checkAndWrap();
    }

    wrapLoadingFunction("showLoading", true);
    wrapLoadingFunction("hideLoading", false);

    // 重写 displayWhoisResult 和 displayError 函数，添加自动滚动和loading控制
    function wrapResultFunction(funcName) {
      let checkCount = 0;
      const maxChecks = 100;

      function checkAndWrap() {
        if (window[funcName]) {
          const original = window[funcName];
          window[funcName] = function (...args) {
            original.apply(this, args);
            setQueryButtonLoading(false);
            // 滚动到结果区域
            setTimeout(function () {
              const resultContainer = document.getElementById("query-result");
              if (resultContainer && resultContainer.innerHTML.trim() !== "") {
                resultContainer.scrollIntoView({
                  behavior: "smooth",
                  block: "start",
                });
              }
            }, 100);
          };
        } else if (checkCount < maxChecks) {
          checkCount++;
          setTimeout(checkAndWrap, 50);
        }
      }

      checkAndWrap();
    }

    wrapResultFunction("displayWhoisResult");
    wrapResultFunction("displayError");

    // 将函数暴露到全局，供其他脚本使用
    window.setQueryButtonLoading = setQueryButtonLoading;
  });
})();
// 自动查询功能（用于直接访问域名URL时自动查询）
(function () {
  "use strict";

  function initAutoQuery() {
    // 仅在首页查询容器存在时启用自动查询，避免旧详情页触发 queryDomain 报错
    const resultContainer = document.getElementById("query-result");
    if (!resultContainer) {
      return;
    }

    let domain = null;

    // 首先检查URL参数（GET参数）
    const urlParams = new URLSearchParams(window.location.search);
    const domainParam = urlParams.get("domain");
    if (domainParam && domainParam.trim()) {
      domain = domainParam.trim();

      // 设置输入框值
      const domainInput = document.getElementById("domain-input");
      if (domainInput) {
        domainInput.value = domain;
      }
    } else {
      // 从 URL 路径中提取域名
      const path = decodeURIComponent(window.location.pathname.replace(/^\//, ""));
      const staticRoutes = new Set([
        "about",
        "contact",
        "api-docs",
        "api",
        "pages",
        "assets",
        "favicon.ico",
      ]);

      const isValidTarget = typeof window.isValidQueryTarget === "function"
        ? window.isValidQueryTarget
        : function (value) {
            return typeof window.isValidDomain === "function" && window.isValidDomain(value);
          };

      // 排除已知路径
      if (
        path &&
        path !== "index.php" &&
        path !== "" &&
        !staticRoutes.has(path.toLowerCase()) &&
        !path.includes(".php") &&
        !path.includes("/")
      ) {
        if (isValidTarget(path)) {
          domain = path;
        }
      }
    }

    if (!domain) {
      return;
    }

    // 使用轮询机制确保 queryDomain 函数可用
    let attempts = 0;
    const maxAttempts = 50; // 最多尝试5秒

    function autoQuery() {
      attempts++;

      if (typeof window.queryDomain === "function") {
        if (!document.getElementById("query-result")) {
          return;
        }
        // 函数已加载，执行查询
        console.log("Auto querying domain:", domain);
        window.queryDomain(domain);
      } else if (attempts < maxAttempts) {
        // 函数还未加载，继续等待
        setTimeout(autoQuery, 100);
      } else {
        console.error("queryDomain function not found after max attempts");
      }
    }

    // 如果 DOM 已加载，立即开始尝试；否则等待 DOMContentLoaded
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", function () {
        setTimeout(autoQuery, 100);
      });
    } else {
      setTimeout(autoQuery, 100);
    }
  }

  // 初始化
  initAutoQuery();
})();
